<?php

namespace Tests\Feature;

use App\Models\NotificationHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotifHistoryGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_web_ditolak(): void
    {
        $this->get(route('notif_histories.index'))
            ->assertRedirect(route('home'));

        $log = NotificationHistory::create([
            'type' => 'test',
            'title' => 'Guard',
            'body' => 'guard',
            'topic' => 'general',
            'channel' => 'fcm_topic',
            'status' => 'success',
            'source' => 'scheduler',
            'sent_at' => now(),
        ]);

        $this->post(route('notif_histories.resend', $log))
            ->assertRedirect(route('home'));
    }

    public function test_guest_api_ditolak_401(): void
    {
        $this->getJson('/api/notif-histories')->assertUnauthorized();
        $this->deleteJson('/api/notif-histories', ['ids' => [1]])->assertUnauthorized();
    }

    public function test_admin_bisa_hapus_satuan_dan_batch(): void
    {
        $auth = [
            'env_authenticated' => true,
            'env_auth_time' => now(),
            'env_auth_hash' => config('envauth.password'),
        ];
        $make = fn () => NotificationHistory::create([
            'type' => 'test',
            'title' => 'Hapus',
            'body' => 'hapus',
            'topic' => 'general',
            'channel' => 'fcm_topic',
            'status' => 'success',
            'source' => 'scheduler',
            'sent_at' => now(),
        ]);

        $one = $make();
        $this->withSession($auth)
            ->deleteJson("/api/notif-histories/{$one->id}")
            ->assertOk();
        $this->assertDatabaseMissing('notification_histories', ['id' => $one->id]);

        $ids = [$make()->id, $make()->id];
        $this->withSession($auth)
            ->deleteJson('/api/notif-histories', ['ids' => $ids])
            ->assertOk()
            ->assertJsonPath('data.deleted_count', 2);
        foreach ($ids as $id) {
            $this->assertDatabaseMissing('notification_histories', ['id' => $id]);
        }
    }
}
