<?php

namespace Tests\Feature;

use App\Models\NotificationHistory;
use App\Services\NtfyServices;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NtfyTest extends TestCase
{
    use RefreshDatabase;

    private ?string $ntfyBackup = null;

    private string $ntfyPath = '';

    protected function setUp(): void
    {
        parent::setUp();

        $this->ntfyPath = NtfyServices::configPath();
        $this->ntfyBackup = is_file($this->ntfyPath) ? (string) file_get_contents($this->ntfyPath) : null;
    }

    protected function tearDown(): void
    {
        if ($this->ntfyBackup !== null) {
            file_put_contents($this->ntfyPath, $this->ntfyBackup);
        } elseif (is_file($this->ntfyPath)) {
            @unlink($this->ntfyPath);
        }

        parent::tearDown();
    }

    private function authSession(): array
    {
        return [
            'env_authenticated' => true,
            'env_auth_time' => now(),
            'env_auth_hash' => config('envauth.password'),
        ];
    }

    public function test_guest_ditolak(): void
    {
        $this->getJson('/api/settings/ntfy')->assertUnauthorized();
        $this->putJson('/api/settings/ntfy', ['topic' => 'abc'])->assertUnauthorized();
        $this->postJson('/api/settings/ntfy/test')->assertUnauthorized();
    }

    public function test_admin_bisa_lihat_dan_ubah_topic(): void
    {
        $this->withSession($this->authSession())
            ->getJson('/api/settings/ntfy')
            ->assertOk()
            ->assertJsonStructure(['data' => ['server', 'topic', 'exists']]);

        $this->withSession($this->authSession())
            ->putJson('/api/settings/ntfy', ['server' => 'https://ntfy.sh', 'topic' => 'who-do-test123'])
            ->assertOk()
            ->assertJsonPath('data.topic', 'who-do-test123');

        $this->assertSame('who-do-test123', NtfyServices::getConfig()['topic']);
    }

    public function test_topic_tidak_valid_ditolak(): void
    {
        $this->withSession($this->authSession())
            ->putJson('/api/settings/ntfy', ['topic' => 'ada spasi!'])
            ->assertStatus(422);

        $this->withSession($this->authSession())
            ->putJson('/api/settings/ntfy', ['topic' => ''])
            ->assertStatus(422);
    }

    public function test_push_ntfy_dari_history_mencatat_log_channel_ntfy(): void
    {
        Http::fake(['*' => Http::response('{"id":"abc"}', 200)]);
        NtfyServices::saveConfig('https://ntfy.sh', 'who-do-test123');

        $log = NotificationHistory::create([
            'type' => 'do_baru',
            'title' => 'DO Baru',
            'body' => 'Isi DO',
            'topic' => 'general',
            'channel' => 'fcm_topic',
            'status' => 'success',
            'source' => 'scheduler',
            'sent_at' => now(),
        ]);

        $this->withSession($this->authSession())
            ->postJson("/api/notif-histories/{$log->id}/push-ntfy")
            ->assertOk()
            ->assertJsonPath('data.channel', 'ntfy');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'ntfy.sh/who-do-test123')
                && $request->hasHeader('Title');
        });

        $this->assertDatabaseHas('notification_histories', [
            'parent_id' => $log->id,
            'channel' => 'ntfy',
            'source' => 'manual',
        ]);
    }

    public function test_send_tidak_pernah_throw(): void
    {
        Http::fake(['*' => Http::response('error', 500)]);

        // Response 500: harus diam (log saja), tanpa exception.
        NtfyServices::send('Judul', 'Isi', 'who-do-test123', 'https://ntfy.sh');
        $this->assertTrue(true);

        // Tanpa topic: harus diam juga.
        NtfyServices::send('Judul', 'Isi', '', 'https://ntfy.sh');
        $this->assertTrue(true);
    }
}
