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
    }
}
