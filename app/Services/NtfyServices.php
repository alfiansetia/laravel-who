<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NtfyServices
{
    public static function configPath(): string
    {
        return storage_path('app/ntfy.json');
    }

    public static function defaultServer(): string
    {
        $server = (string) config('services.ntfy.server', 'https://ntfy.sh');

        return $server !== '' ? $server : 'https://ntfy.sh';
    }

    /**
     * Baca config ntfy (storage/app/ntfy.json): server + topic.
     * Disimpan di file agar bisa diganti tanpa deploy/DB.
     *
     * @return array{server: string, topic: string, exists: bool}
     */
    public static function getConfig(): array
    {
        $path = static::configPath();

        if (! is_file($path)) {
            return ['server' => static::defaultServer(), 'topic' => '', 'exists' => false];
        }

        $decoded = json_decode((string) @file_get_contents($path), true);

        if (! is_array($decoded)) {
            return ['server' => static::defaultServer(), 'topic' => '', 'exists' => true];
        }

        $server = trim((string) ($decoded['server'] ?? ''));
        $topic = trim((string) ($decoded['topic'] ?? ''));

        return [
            'server' => $server !== '' ? $server : static::defaultServer(),
            'topic' => $topic,
            'exists' => true,
        ];
    }

    /**
     * @return array{server: string, topic: string, exists: bool}
     */
    public static function saveConfig(string $server, string $topic): array
    {
        $server = rtrim(trim($server), '/');
        $topic = trim($topic);

        if ($server === '') {
            $server = static::defaultServer();
        }

        File::put(
            static::configPath(),
            json_encode(['server' => $server, 'topic' => $topic], JSON_PRETTY_PRINT)
        );

        return ['server' => $server, 'topic' => $topic, 'exists' => true];
    }

    /**
     * Aturan nama topic ntfy: huruf, angka, underscore, dash, maks 64 karakter.
     * Return pesan error atau null bila valid.
     */
    public static function validateTopic(string $topic): ?string
    {
        $topic = trim($topic);

        if ($topic === '') {
            return 'Topic ntfy wajib diisi.';
        }

        if (strlen($topic) > 64) {
            return 'Topic ntfy maksimal 64 karakter.';
        }

        if (! preg_match('/^[-_A-Za-z0-9]+$/', $topic)) {
            return 'Topic ntfy hanya boleh huruf, angka, underscore, dan dash.';
        }

        return null;
    }

    /**
     * Push judul + isi ke topic ntfy. Fire-and-forget: tidak me-return
     * apa-apa dan tidak pernah melempar exception ke caller.
     * Kegagalan hanya tercatat di log.
     */
    public static function send(string $title, string $body, ?string $topic = null, ?string $server = null): void
    {
        try {
            $config = static::getConfig();
            $topic = trim((string) ($topic ?? $config['topic']));
            $server = rtrim(trim((string) ($server ?? $config['server'])), '/');

            if ($topic === '') {
                Log::warning('Ntfy: Topic belum dikonfigurasi, pengiriman dibatalkan');

                return;
            }

            if ($server === '') {
                $server = static::defaultServer();
            }

            $post = Http::timeout(10)
                ->withHeaders([
                    'Title' => mb_substr($title, 0, 200),
                    'Priority' => 'high',
                    'Tags' => 'package',
                ])
                ->withBody($body, 'text/plain')
                ->post($server.'/'.$topic);

            if (! $post->successful()) {
                Log::warning('Ntfy: Gagal mengirim notifikasi', [
                    'topic' => $topic,
                    'status_code' => $post->status(),
                    'response' => mb_substr($post->body(), 0, 1000),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Ntfy: Exception saat mengirim notifikasi', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
