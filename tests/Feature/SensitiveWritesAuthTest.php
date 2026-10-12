<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SensitiveWritesAuthTest extends TestCase
{
    use RefreshDatabase;

    private function authSession(): array
    {
        return [
            'env_authenticated' => true,
            'env_auth_time' => now(),
            'env_auth_hash' => config('envauth.password'),
        ];
    }

    public function test_guest_cannot_sync_products(): void
    {
        // Middleware menolak sebelum ProductServices::getAll() (Odoo) dipanggil.
        $this->postJson('/api/product-sync')->assertUnauthorized();
    }

    public function test_guest_cannot_sync_spreadsheet(): void
    {
        $this->postJson('/api/spreadsheet/sync-all')->assertUnauthorized();
        $this->postJson('/api/spreadsheet', [
            'code' => 'X',
            'p' => '1.00',
            'l' => '1.00',
            't' => '1.00',
            'b' => '1.00',
        ])->assertUnauthorized();
    }

    public function test_guest_cannot_upload_file_index(): void
    {
        $this->postJson('/tools/file-search/upload')->assertUnauthorized();
    }

    public function test_guest_can_still_read_spreadsheet_and_search(): void
    {
        $this->getJson('/api/spreadsheet')->assertOk();
        $this->getJson('/api/products/search?q=A')->assertOk();
    }

    public function test_authenticated_upload_without_file_fails_validation(): void
    {
        // 422 = middleware lolos, validasi menolak (tanpa efek samping file).
        $this->withSession($this->authSession())
            ->postJson('/tools/file-search/upload')
            ->assertStatus(422);
    }
}
