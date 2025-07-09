<?php

namespace App\Console\Commands;

use App\Services\Odoo;
use App\Services\OdooSession;
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
            $profile = Odoo::getProfile();
            $this->info('Session Valid!');
            return;
        } catch (\Throwable $th) {
            $this->info('Session Invalid, otw Login!');
            $this->login();
            return;
        }
    }

    public function login()
    {
        try {
            $db = Odoo::getDB();
            $baseUrl = Odoo::getBaseUrl();

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
                    'csrf_token'    => $csrfToken,
                    'db'            => $db,
                    'login'         => Odoo::getEmail(),
                    'password'      => Odoo::getPassword(),
                ],
            ]);

            $html2 = $client->get($baseUrl . '/web?')->getBody()->getContents();
            // Cari isi session_info dengan regex
            if (preg_match('/odoo\.session_info\s*=\s*(\{.*?\});/s', $html2, $matches)) {
                $json = $matches[1];
                // Decode JSON jadi array PHP
                $session_info = json_decode($json, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $session_id = $session_info['session_id'];
                    $data = [
                        'session_id'            => $session_info['session_id'],
                        'uid'                   => $session_info['uid'],
                        'db'                    => $session_info['db'],
                        'name'                  => $session_info['name'],
                        'username'              => $session_info['username'],
                        'partner_display_name'  => $session_info['partner_display_name'],
                        'partner_id'            => $session_info['partner_id'],
                    ];
                    OdooSession::saveSession($data);
                    TelegramServices::sendToGroup('Success Login, session : ' . $session_id);
                    $this->info('Session ID berhasil disimpan: ' . $session_id);
                } else {
                    $this->error("Gagal decode session_info JSON: " . json_last_error_msg());
                }
            } else {
                $this->error("session_info tidak ditemukan di HTML");
            }
            // // 4. Ambil session_id dari cookie
            // $cookies = $client->getConfig('cookies');
            // $sessionId = '';
            // foreach ($cookies->toArray() as $cookie) {
            //     if ($cookie['Name'] === 'session_id') {
            //         $sessionId = $cookie['Value'];
            //         break;
            //     }
            // }

            // if (!$sessionId) {
            //     $this->error('Gagal mendapatkan session_id');
            //     return 1;
            // }
            return 0;
        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan: ' . $e->getMessage());
            return 1;
        }
    }
}
