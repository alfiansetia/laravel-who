<?php

namespace Tests\Feature;

use App\Models\NotificationHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotifHistoryPollTest extends TestCase
{
    use RefreshDatabase;

    private function makeLog(array $overrides = []): NotificationHistory
    {
        return NotificationHistory::create(array_merge([
            'type' => 'do_baru',
            'title' => 'DO Baru',
            'body' => 'Ada DO baru',
            'topic' => 'general',
            'channel' => 'fcm_topic',
            'status' => 'success',
            'source' => 'scheduler',
            'sent_at' => now(),
        ], $overrides));
    }

    public function test_poll_bisa_diakses_tanpa_login(): void
    {
        $this->makeLog();

        $this->getJson('/api/notif-histories/poll')
            ->assertOk()
            ->assertJsonStructure(['data', 'latest_id', 'count', 'has_more']);
    }

    public function test_poll_filter_since_id_urut_asc(): void
    {
        $first = $this->makeLog(['title' => 'Satu']);
        $this->makeLog(['title' => 'Dua']);
        $this->makeLog(['title' => 'Tiga']);

        $res = $this->getJson("/api/notif-histories/poll?since_id={$first->id}&limit=10")
            ->assertOk();

        $this->assertSame(2, $res->json('count'));
        $this->assertSame(['Dua', 'Tiga'], $res->json('data.*.title'));
        $this->assertFalse($res->json('has_more'));
    }

    public function test_poll_kosong_kembalikan_since_id(): void
    {
        $res = $this->getJson('/api/notif-histories/poll?since_id=999&limit=10')
            ->assertOk();

        $this->assertSame(0, $res->json('count'));
        $this->assertSame([], $res->json('data'));
        $this->assertSame(999, $res->json('latest_id'));
    }

    public function test_poll_validasi_input(): void
    {
        $this->getJson('/api/notif-histories/poll?since_id=-1')->assertStatus(422);
        $this->getJson('/api/notif-histories/poll?limit=100')->assertStatus(422);
    }

    public function test_poll_rate_limit_5_per_menit(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->getJson('/api/notif-histories/poll')->assertOk();
        }

        $this->getJson('/api/notif-histories/poll')->assertStatus(429);
    }
}
