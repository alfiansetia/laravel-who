<?php

namespace App\Console\Commands;

use App\Services\Odoo;
use App\Services\OdooSession;
use App\Services\TelegramServices;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class OdooLogin extends Command
{
    protected $signature = 'app:odoo-login';
    protected $description = 'Login ke Odoo dan ambil session_id';

    public function handle()
    {
        try {
            $profile = Odoo::getProfile();
            $this->info('Session Valid!');
            return 0;
        } catch (\Throwable $th) {
            $this->info('Session Invalid, otw Login!');
            return $this->login();
        }
    }

    public function login()
    {
        try {
            $db = Odoo::getDB();
            $baseUrl = Odoo::getBaseUrl();

            if (empty($db) || empty($baseUrl)) {
                $this->error('Konfigurasi Odoo tidak lengkap');
                return 1;
            }

            // 1. Ambil halaman login dengan timeout 15 detik
            $client = new Client([
                'cookies' => true,
                'timeout' => 15,
                'connect_timeout' => 10
            ]);

            $this->info('Mengambil halaman login...');
            $loginPage = $client->get($baseUrl . '/web?db=' . $db);
            $html = $loginPage->getBody();

            // 2. Ambil csrf_token dari HTML
            $crawler = new Crawler($html);
            $csrfInput = $crawler->filter('input[name="csrf_token"]');

            if ($csrfInput->count() === 0) {
                $this->error('CSRF token tidak ditemukan di halaman login');
                return 1;
            }

            $csrfToken = $csrfInput->attr('value');
            $this->info("csrf_token => " . $csrfToken);

            // 3. Kirim POST login dengan cookies
            $this->info('Mengirim request login...');
            $res = $client->post($baseUrl . '/web/login', [
                'form_params' => [
                    'csrf_token'    => $csrfToken,
                    'db'            => $db,
                    'login'         => Odoo::getEmail(),
                    'password'      => Odoo::getPassword(),
                ],
            ]);

            $this->info('Mengambil session info...');
            $html2 = $client->get($baseUrl . '/web?')->getBody()->getContents();

            // Cari isi session_info dengan regex
            if (preg_match('/odoo\.session_info\s*=\s*(\{.*?\});/s', $html2, $matches)) {
                $json = $matches[1];
                // Decode JSON jadi array PHP
                $session_info = json_decode($json, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    // Validasi data session menggunakan Arr::get untuk safe access
                    $session_id = Arr::get($session_info, 'session_id');
                    $uid = Arr::get($session_info, 'uid');

                    if (empty($session_id) || empty($uid)) {
                        $this->error('Session ID atau UID kosong');
                        return 1;
                    }

                    $data = [
                        'session_id'            => Arr::get($session_info, 'session_id'),
                        'uid'                   => Arr::get($session_info, 'uid'),
                        'db'                    => Arr::get($session_info, 'db'),
                        'name'                  => Arr::get($session_info, 'name'),
                        'username'              => Arr::get($session_info, 'username'),
                        'partner_display_name'  => Arr::get($session_info, 'partner_display_name'),
                        'partner_id'            => Arr::get($session_info, 'partner_id'),
                    ];

                    OdooSession::saveSession($data);
                    $url = config('app.url');

                    // Kirim notifikasi Telegram (sudah ada error handling internal)
                    TelegramServices::sendToGroup('Success Login on : ' . $url . ', session : ' . $session_id);

                    $this->info('Session ID berhasil disimpan: ' . $session_id);
                    return 0;
                } else {
                    $this->error("Gagal decode session_info JSON: " . json_last_error_msg());
                    return 1;
                }
            } else {
                $this->error("session_info tidak ditemukan di HTML");
                return 1;
            }
        } catch (\Exception $e) {
            // Kirim notifikasi error (sudah ada error handling internal)
            TelegramServices::sendToGroup('Error Login : ' . $e->getMessage());

            $this->error('Terjadi kesalahan: ' . $e->getMessage());
            return 1;
        }
    }
}
