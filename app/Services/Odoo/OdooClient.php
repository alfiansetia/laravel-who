<?php

namespace App\Services\Odoo;

use App\Exceptions\OdooException;
use App\Services\OdooSession;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

/**
 * Single entry point untuk semua request JSON-RPC ke ERP Odoo.
 *
 * ERP Odoo hanya punya 3 bentuk endpoint, sisanya parameter:
 * - POST /web/dataset/search_read
 * - POST /web/dataset/call_kw/{model}/{method} (read, read_group, write, ...)
 * - POST /web/session/get_session_info
 *
 * Instance-based (bukan static) agar aman untuk Octane/queue/tests.
 */
class OdooClient
{
    public function __construct(
        private ?string $baseUrl = null,
        private ?int $timeout = null,
        private ?int $connectTimeout = null,
        private ?int $retryTimes = null,
        private ?int $retrySleepMs = null,
    ) {}

    /**
     * @return array{records: array, length: int}
     */
    public function searchRead(
        string $model,
        array $domain = [],
        array $fields = ['id', 'display_name'],
        int $limit = 80,
        int $offset = 0,
        string $sort = '',
        array $context = [],
    ): array {
        $json = $this->postJson('/web/dataset/search_read', [
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => [
                'model' => $model,
                'domain' => array_values($domain),
                'fields' => $fields,
                'limit' => max($limit, 1),
                'offset' => max($offset, 0),
                'sort' => $sort,
                'context' => $this->defaultContext($context),
            ],
        ], idempotent: true);

        $result = Arr::get($json, 'result');

        if (! is_array($result)) {
            throw new OdooException('Format search_read Odoo tidak valid', 500, $json);
        }

        return $result;
    }

    /**
     * @param  array<int>  $ids
     */
    public function read(string $model, array $ids, array $fields, array $context = []): array
    {
        $ids = $this->normalizeIds($ids);

        if ($ids === []) {
            return [];
        }

        $json = $this->postJson("/web/dataset/call_kw/{$model}/read", [
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => [
                'model' => $model,
                'method' => 'read',
                'args' => [$ids, $fields],
                'kwargs' => ['context' => $this->defaultContext($context)],
            ],
        ], idempotent: true);

        return Arr::get($json, 'result', []);
    }

    public function readOne(string $model, int $id, array $fields, array $context = []): array
    {
        $record = Arr::get($this->read($model, [$id], $fields, $context), '0');

        if (! $record) {
            throw new OdooException("Data {$model} #{$id} tidak ditemukan", 404);
        }

        return $record;
    }

    public function readGroup(
        string $model,
        array $domain,
        array $fields,
        array $groupby,
        array $kwargs = [],
        array $context = [],
    ): array {
        $json = $this->postJson("/web/dataset/call_kw/{$model}/read_group", [
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => [
                'model' => $model,
                'method' => 'read_group',
                'args' => [],
                'kwargs' => array_merge([
                    'domain' => array_values($domain),
                    'fields' => $fields,
                    'groupby' => $groupby,
                    'context' => $this->defaultContext($context),
                ], $kwargs),
            ],
        ], idempotent: true);

        return Arr::get($json, 'result', []);
    }

    public function write(string $model, int $id, array $values, array $context = []): mixed
    {
        $json = $this->postJson("/web/dataset/call_kw/{$model}/write", [
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => [
                'model' => $model,
                'method' => 'write',
                'args' => [$id, $values],
                'kwargs' => ['context' => $this->defaultContext($context)],
            ],
        ], idempotent: false);

        return Arr::get($json, 'result');
    }

    public function getSessionInfo(?string $sessionId = null): array
    {
        return $this->postJson('/web/session/get_session_info', [
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => [],
        ], idempotent: true, sessionId: $sessionId);
    }

    /**
     * @return array JSON-RPC mentah (sudah lolos cek error Odoo).
     */
    public function postJson(string $path, array $payload, bool $idempotent = true, ?string $sessionId = null): array
    {
        $baseUrl = rtrim($this->baseUrl ?? (string) config('services.odoo.base_url'), '/');

        if ($baseUrl === '') {
            throw new OdooException('Odoo Base URL tidak dikonfigurasi', 500);
        }

        $session = $sessionId ?? (string) Arr::get(OdooSession::getCurrentSession(), 'session_id', '');

        $http = Http::connectTimeout($this->connectTimeout ?? (int) config('services.odoo.connect_timeout', 5))
            ->timeout($this->timeout ?? (int) config('services.odoo.timeout', 15))
            ->withHeaders([
                'accept' => 'application/json, text/javascript, */*; q=0.01',
                'accept-language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
                'content-type' => 'application/json',
                'x-requested-with' => 'XMLHttpRequest',
                'Cookie' => 'session_id='.$session,
            ]);

        if ($idempotent) {
            $http = $http->retry(
                $this->retryTimes ?? (int) config('services.odoo.retry_times', 2),
                $this->retrySleepMs ?? (int) config('services.odoo.retry_sleep_ms', 200),
                fn (RequestException|ConnectionException $e) => $e instanceof ConnectionException
                    || ($e instanceof RequestException
                        && ($e->response === null || $e->response->serverError() || $e->response->status() === 429)),
                throw: false,
            );
        }

        try {
            $response = $http->post($baseUrl.$path, $payload);
        } catch (ConnectionException $e) {
            throw new OdooException('Koneksi ke Odoo gagal', 500, ['detail' => $e->getMessage()]);
        }

        $json = $response->json();

        if (! is_array($json)) {
            throw new OdooException('Respons Odoo bukan JSON', $response->status(), [
                'status' => $response->status(),
                'body' => mb_substr($response->body(), 0, 2000),
            ]);
        }

        if (Arr::exists($json, 'error')) {
            throw new OdooException(
                'Odoo Internal Error: '.Arr::get($json, 'error.message', 'Unknown'),
                500,
                $json,
            );
        }

        if (! $response->successful()) {
            throw new OdooException('Odoo API Error', $response->status(), $json);
        }

        return $json;
    }

    public function defaultContext(array $extra = []): array
    {
        // UID selalu milik session Odoo yang sedang login (session.json),
        // bukan env — tiap user/login punya identitas sendiri.
        $uid = (int) (Arr::get(OdooSession::getCurrentSession(), 'uid') ?? 0);

        return array_merge([
            'lang' => config('services.odoo.lang', 'en_US'),
            'tz' => config('app.timezone', 'UTC'),
            'uid' => $uid,
        ], $extra);
    }

    /**
     * @return array<int>
     */
    private function normalizeIds(array $ids): array
    {
        return array_values(array_unique(array_map(
            'intval',
            array_filter($ids, 'is_numeric'),
        )));
    }
}
