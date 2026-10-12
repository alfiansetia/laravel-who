<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IzinEdarAuthTest extends TestCase
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

    public function test_guest_cannot_delete_by_kategori(): void
    {
        $this->deleteJson('/api/izin-edars/kategori/PKD')->assertUnauthorized();
    }

    public function test_guest_cannot_delete_file(): void
    {
        $this->deleteJson('/api/izin-edars/files/PKD')->assertUnauthorized();
    }

    public function test_guest_cannot_import_batch(): void
    {
        $this->postJson('/api/izin-edars/import-batch', [
            'kategori' => 'PKD',
            'rows' => [['nomor_izin_edar' => 'TEST-001']],
        ])->assertUnauthorized();
    }

    public function test_guest_cannot_start_sync(): void
    {
        $this->postJson('/api/izin-edars/sync')->assertUnauthorized();
    }

    public function test_guest_cannot_stop_or_reset_sync(): void
    {
        $this->postJson('/api/izin-edars/sync/stop')->assertUnauthorized();
        $this->deleteJson('/api/izin-edars/sync/reset')->assertUnauthorized();
    }

    public function test_guest_can_still_read_progress_and_index(): void
    {
        $this->getJson('/api/izin-edars/sync/progress')->assertOk();
        $this->getJson('/api/izin-edars')->assertOk();
    }

    public function test_authenticated_can_delete_empty_kategori(): void
    {
        $this->withSession($this->authSession())
            ->deleteJson('/api/izin-edars/kategori/PKD')
            ->assertOk()
            ->assertJsonPath('success', true);
    }
}
