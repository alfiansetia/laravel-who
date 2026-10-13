<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class MonitorStateTest extends TestCase
{
    private function authed()
    {
        return $this->withSession([
            'env_authenticated' => true,
            'env_auth_time' => now(),
            'env_auth_hash' => config('envauth.password'),
        ]);
    }

    public function test_guest_ditolak_401(): void
    {
        $this->getJson('/api/settings/monitor')->assertUnauthorized();
        $this->putJson('/api/settings/monitor', ['length' => 1])->assertUnauthorized();
    }

    public function test_show_dan_update_length(): void
    {
        $path = storage_path('app/monitor.json');
        $backup = is_file($path) ? File::get($path) : null;

        try {
            File::put($path, json_encode(['length' => 17521, 'last_error' => '2026-10-09T08:06:00.733720Z'], JSON_PRETTY_PRINT));

            $this->authed()->getJson('/api/settings/monitor')
                ->assertOk()
                ->assertJsonPath('data.length', 17521)
                ->assertJsonPath('data.valid', true);

            $this->authed()->putJson('/api/settings/monitor', ['length' => 17520])
                ->assertOk()
                ->assertJsonPath('data.length', 17520);

            $saved = json_decode(File::get($path), true);
            $this->assertSame(17520, $saved['length']);
            $this->assertSame('2026-10-09T08:06:00.733720Z', $saved['last_error']);
        } finally {
            if ($backup !== null) {
                File::put($path, $backup);
            } else {
                @unlink($path);
            }
        }
    }

    public function test_update_validasi_length(): void
    {
        $this->authed()->putJson('/api/settings/monitor', ['length' => -1])->assertUnprocessable();
        $this->authed()->putJson('/api/settings/monitor', [])->assertUnprocessable();
    }
}
