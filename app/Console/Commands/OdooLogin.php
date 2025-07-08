<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Services\TelegramServices;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Symfony\Component\DomCrawler\Crawler;

class OdooLogin extends Command
{
    protected $signature = 'app:odoo-login';
    protected $description = 'Login ke Odoo dan ambil session_id';

    public function handle()
    {
        try {
            $email = config('services.odoo.email');
            $password = config('services.odoo.password');
            $db = config('services.odoo.db');
            $baseUrl = config('services.odoo.base_url');

            // 1. Ambil halaman login
            $client = new Client(['cookies' => true]);
            $loginPage = $client->get($baseUrl . '/web?db=' . $db);
            $html = $loginPage->getBody();
            // 2. Ambil csrf_token dari HTML
            $crawler = new Crawler($html);
            $csrfToken = $crawler->filter('input[name="csrf_token"]')->attr('value');
            $this->info("csrf_token => " . $csrfToken);
            // 3. Kirim POST login dengan cookies
            $res = $client->post($baseUrl . '/web/login', [
                'form_params' => [
                    'csrf_token' => $csrfToken,
                    'db' => $db,
                    'login' => $email,
                    'password' => $password
                ],
            ]);
            // 4. Ambil session_id dari cookie
            $cookies = $client->getConfig('cookies');
            $sessionId = '';
            foreach ($cookies->toArray() as $cookie) {
                if ($cookie['Name'] === 'session_id') {
                    $sessionId = $cookie['Value'];
                    break;
                }
            }

            if (!$sessionId) {
                $this->error('Gagal mendapatkan session_id');
                return 1;
            }
            $setting = Setting::first();
            if (!$setting) {
                Setting::create([
                    'odoo_session' => $sessionId
                ]);
            } else {
                $setting->update([
                    'odoo_session' => $sessionId
                ]);
            }
            TelegramServices::sendToGroup('Success Login, session : ' . $sessionId);
            $this->info('Session ID berhasil disimpan: ' . $sessionId);
            return 0;
        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan: ' . $e->getMessage());
            return 1;
        }
    }
}
