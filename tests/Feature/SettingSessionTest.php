<?php

namespace Tests\Feature;

use App\Services\OdooSession;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SettingSessionTest extends TestCase
{
    public function test_store_persists_new_session_id(): void
    {
        $path = OdooSession::getSessionFile();
        $backup = is_file($path) ? File::get($path) : null;

        try {
            $this->withSession([
                'env_authenticated' => true,
                'env_auth_time' => now(),
                'env_auth_hash' => config('envauth.password'),
            ])->postJson('/api/settings/', ['env_value' => 'BARU123TOKEN'])
                ->assertOk();

            $saved = OdooSession::getCurrentSession();

            $this->assertSame('BARU123TOKEN', $saved['session_id']);
        } finally {
            if ($backup !== null) {
                File::put($path, $backup);
            }
        }
    }

    public function test_store_syncs_identity_from_session_info(): void
    {
        $path = OdooSession::getSessionFile();
        $backup = is_file($path) ? File::get($path) : null;

        try {
            Http::fake([
                '*get_session_info*' => Http::response([
                    'jsonrpc' => '2.0',
                    'id' => null,
                    'result' => [
                        'session_id' => 'BARU123TOKEN',
                        'uid' => 777,
                        'db' => 'MAP_LIVE',
                        'name' => 'User Baru',
                        'username' => 'baru@example.com',
                        'partner_display_name' => 'Partner Baru',
                        'partner_id' => 12345,
                    ],
                ], 200),
            ]);

            $this->withSession([
                'env_authenticated' => true,
                'env_auth_time' => now(),
                'env_auth_hash' => config('envauth.password'),
            ])->postJson('/api/settings/', ['env_value' => 'BARU123TOKEN'])
                ->assertOk()
                ->assertJsonPath('data.verified', true);

            $saved = OdooSession::getCurrentSession();

            $this->assertSame('BARU123TOKEN', $saved['session_id']);
            $this->assertSame(777, $saved['uid']);
            $this->assertSame('User Baru', $saved['name']);
            $this->assertSame('baru@example.com', $saved['username']);
            $this->assertSame('Partner Baru', $saved['partner_display_name']);
            $this->assertSame(12345, $saved['partner_id']);
        } finally {
            if ($backup !== null) {
                File::put($path, $backup);
            }
        }
    }

    public function test_store_keeps_manual_session_when_odoo_rejects_it(): void
    {
        $path = OdooSession::getSessionFile();
        $backup = is_file($path) ? File::get($path) : null;

        try {
            Http::fake([
                '*get_session_info*' => Http::response([
                    'jsonrpc' => '2.0',
                    'id' => null,
                    'error' => ['code' => 100, 'message' => 'Odoo Session Expired'],
                ], 200),
            ]);

            $this->withSession([
                'env_authenticated' => true,
                'env_auth_time' => now(),
                'env_auth_hash' => config('envauth.password'),
            ])->postJson('/api/settings/', ['env_value' => 'TOKENINVALID999'])
                ->assertOk()
                ->assertJsonPath('data.verified', false);

            // Session tempel manual tidak boleh hilang/tertimpa walau ditolak Odoo.
            $this->assertSame('TOKENINVALID999', OdooSession::getCurrentSession()['session_id']);
        } finally {
            if ($backup !== null) {
                File::put($path, $backup);
            }
        }
    }

    public function test_guest_web_settings_redirects_to_home(): void
    {
        $response = $this->get('/settings/');

        $response->assertRedirect(route('home'));
    }

    public function test_guest_web_redirect_remembers_intended_url(): void
    {
        $response = $this->get('/settings/');

        $response->assertRedirect(route('home'));
        $this->assertSame(url('/settings/'), session('url.intended'));
    }

    public function test_verify_returns_sanitized_intended_url(): void
    {
        config()->set('envauth.password', Hash::make('rahasia'));

        $response = $this->withSession(['url.intended' => url('/settings/')])
            ->postJson('/api/auth/verify', ['password' => 'rahasia'])
            ->assertOk();

        $response->assertJsonPath('data.auth', true);
        $response->assertJsonPath('data.intended', '/settings');
        $this->assertNull(session('url.intended'));
    }

    public function test_verify_rejects_external_intended_url(): void
    {
        config()->set('envauth.password', Hash::make('rahasia'));

        $response = $this->withSession(['url.intended' => 'https://evil.test/phish'])
            ->postJson('/api/auth/verify', ['password' => 'rahasia'])
            ->assertOk();

        $response->assertJsonPath('data.auth', true);
        $response->assertJsonPath('data.intended', null);
    }

    public function test_guest_api_settings_returns_401_json(): void
    {
        $this->postJson('/api/settings/', ['env_value' => 'X'])
            ->assertUnauthorized()
            ->assertJson(['message' => 'Session expired or unauthorized.']);
    }

    public function test_store_persists_when_file_is_in_default_state(): void
    {
        $path = OdooSession::getSessionFile();
        $backup = is_file($path) ? File::get($path) : null;

        try {
            OdooSession::setDefaultSession();

            $this->withSession([
                'env_authenticated' => true,
                'env_auth_time' => now(),
                'env_auth_hash' => config('envauth.password'),
            ])->postJson('/api/settings/', ['env_value' => 'TOKENBARU999'])
                ->assertOk();

            // Token tempel manual tidak boleh terhapus saat dibaca balik.
            $this->assertSame('TOKENBARU999', OdooSession::getCurrentSession()['session_id']);
        } finally {
            if ($backup !== null) {
                File::put($path, $backup);
            }
        }
    }
}
