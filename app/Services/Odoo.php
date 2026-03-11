<?php

namespace App\Services;

use App\Exceptions\OdooException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use Exception;

class Odoo
{
    protected array $data = [];
    protected array $headers = [];
    protected bool $asFile = false;
    protected string $urlParam = '';
    protected string $method = 'GET';
    protected ?string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.odoo.base_url');
        $this->reset();
    }

    /**
     * Reset state untuk request baru atau inisialisasi awal.
     */
    public function reset(): self
    {
        $this->data = [];
        $this->headers = [
            'accept'            => 'application/json, text/javascript, */*; q=0.01',
            'accept-language'   => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
            'content-type'      => 'application/json',
            'x-requested-with'  => 'XMLHttpRequest',
            'Accept-Encoding'   => 'gzip, deflate',
        ];
        $this->asFile = false;
        $this->urlParam = '';
        $this->method = 'GET';

        $this->setCookie();

        return $this;
    }

    /**
     * Ambil session ID dari OdooSession dan masukkan ke Header Cookie.
     */
    public function setCookie(): self
    {
        $session = OdooSession::getCurrentSession();
        if ($sessionId = Arr::get($session, 'session_id')) {
            $this->headers['Cookie'] = 'session_id=' . $sessionId;
        }
        return $this;
    }

    // --- Config Getters ---
    public function getBaseUrl()
    {
        return config('services.odoo.base_url');
    }
    public function getEmail()
    {
        return config('services.odoo.email');
    }
    public function getPassword()
    {
        return config('services.odoo.password');
    }
    public function getDB()
    {
        return config('services.odoo.db');
    }
    public function getUID()
    {
        return Arr::get(OdooSession::getCurrentSession(), 'uid', 0);
    }

    // --- Fluent Setters ---

    public function withData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function withUrlParam(string $urlParam): self
    {
        $this->urlParam = $urlParam;
        return $this;
    }

    public function method(string $method): self
    {
        $this->method = strtoupper($method);
        return $this;
    }

    public function asJson(): self
    {
        $this->asFile = false;
        return $this;
    }

    public function asFile(): self
    {
        $this->asFile = true;
        // Reset headers as in original behavior for file requests
        $this->headers = [];
        $this->setCookie();
        return $this;
    }

    /**
     * Helper utama untuk JSON-RPC Odoo.
     * Sangat memudahkan untuk memanggil method di model Odoo.
     */
    public function call(string $model, string $method, array $args = [], array $kwargs = []): mixed
    {
        $params = [
            "jsonrpc" => "2.0",
            "method" => "call",
            "params" => [
                "model" => $model,
                "method" => $method,
                "args" => $args,
                "kwargs" => array_merge([
                    "context" => [
                        "lang" => "en_US",
                        "tz" => "Asia/Jakarta",
                        "uid" => $this->getUID(),
                    ]
                ], $kwargs)
            ],
        ];

        return $this->asJson()
            ->withUrlParam("/web/dataset/call_kw/{$model}/{$method}")
            ->withData($params)
            ->method('POST')
            ->send();
    }

    /**
     * Alias untuk send() demi kompatibilitas kode lama.
     */
    public function get()
    {
        return $this->send();
    }

    /**
     * Eksekusi request HTTP.
     */
    public function send()
    {
        if (empty($this->baseUrl)) {
            Log::warning('Odoo: Base URL tidak dikonfigurasi');
            throw new OdooException('Odoo Base URL tidak dikonfigurasi', 500);
        }

        $url = $this->baseUrl . $this->urlParam;

        try {
            $http = Http::timeout(15)->withHeaders($this->headers);

            $response = match ($this->method) {
                'POST' => $http->post($url, $this->data),
                default => $http->get($url),
            };

            if (!$response->successful()) {
                Log::warning('Odoo: API request gagal', [
                    'url' => $url,
                    'method' => $this->method,
                    'status_code' => $response->status()
                ]);

                throw new OdooException(
                    'Odoo API Error',
                    $response->status(),
                    $response->json() ?? $response->body()
                );
            }

            if ($this->asFile) {
                return $response;
            }

            $json = $response->json();

            // Cek error internal Odoo (JSON-RPC sering return 200 tapi isinya error)
            if ($this->method === 'POST' && isset($json['error'])) {
                throw new OdooException(
                    "Odoo Internal Error: " . ($json['error']['message'] ?? 'Unknown'),
                    $response->status(),
                    $json['error']
                );
            }

            return $json;
        } catch (OdooException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::error('Odoo: Connection error', [
                'error' => $e->getMessage(),
                'url' => $url
            ]);

            throw new OdooException(
                'Odoo Connection Error: ' . $e->getMessage(),
                500,
                ['original_error' => $e->getMessage()]
            );
        }
    }

    /**
     * Contoh penggunaan method call() untuk mengambil profile.
     */
    public function getProfile()
    {
        return $this->call('res.users', 'read', [
            [$this->getUID()]
        ], [
            'fields' => [
                "image",
                "__last_update",
                "name",
                "lang",
                "tz",
                "tz_offset",
                "company_id",
                "notification_type",
                "odoobot_state",
                "email",
                "signature",
                "display_name"
            ]
        ]);
    }

    /**
     * Magic method untuk mendukung pemanggilan static secara transparan.
     */
    public static function __callStatic($name, $arguments)
    {
        return (new static)->$name(...$arguments);
    }
}
