<?php

namespace App\Console\Commands;

use App\Models\FcmToken;
use App\Services\FirebaseServices;
use Illuminate\Console\Command;

class SubscribeFcmTopic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:subscribe-fcm-topic
                            {--topic= : Paksa semua token ke topic ini (default: pakai topic masing-masing, kosong = general)}
                            {--dry-run : Hanya tampilkan rencana tanpa subscribe}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill: subscribe semua token FCM lama ke topic agar sendToTopic() menjangkau mereka';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $forcedTopic = $this->option('topic') ?: null;
        $dryRun = (bool) $this->option('dry-run');

        if ($forcedTopic) {
            $forcedTopic = FirebaseServices::sanitizeTopic($forcedTopic);
            if ($forcedTopic === '') {
                $this->error('Nama topic tidak valid.');
                return 1;
            }
        }

        // Kelompokkan token per topic (baca per chunk agar hemat memori).
        $groups = [];
        $emptyTopicIds = [];

        FcmToken::query()->select(['id', 'token', 'topic'])->orderBy('id')->chunk(1000, function ($rows) use (&$groups, &$emptyTopicIds, $forcedTopic) {
            foreach ($rows as $row) {
                if (empty($row->token)) {
                    continue;
                }
                $topic = $forcedTopic ?: ($row->topic ?: 'general');
                $groups[$topic][] = $row->token;
                if (empty($row->topic) && ! $forcedTopic) {
                    $emptyTopicIds[] = $row->id;
                }
            }
        });

        $total = array_sum(array_map('count', $groups));

        if ($total === 0) {
            $this->info('Tidak ada token di database.');
            return 0;
        }

        $this->info("Total token: {$total} dalam " . count($groups) . ' topic:');
        foreach ($groups as $topic => $tokens) {
            $this->line("  - {$topic}: " . count($tokens) . ' token');
        }

        if ($dryRun) {
            $this->info('Dry-run: tidak ada yang di-subscribe.');
            return 0;
        }

        // Normalisasi topic kosong di DB agar konsisten ke depannya.
        if (! empty($emptyTopicIds)) {
            FcmToken::whereIn('id', $emptyTopicIds)->update(['topic' => 'general']);
            $this->info(count($emptyTopicIds) . ' baris dengan topic kosong dinormalisasi ke "general".');
        }
        if ($forcedTopic) {
            FcmToken::query()->update(['topic' => $forcedTopic]);
            $this->info("Semua baris di-update ke topic \"{$forcedTopic}\".");
        }

        $failed = 0;
        foreach ($groups as $topic => $tokens) {
            $this->info("Subscribe " . count($tokens) . " token ke \"{$topic}\"...");
            $ok = FirebaseServices::subscribeTopic($tokens, $topic);
            $this->line($ok ? '  OK' : '  <fg=red>GAGAL (cek log)</>');
            if (! $ok) {
                $failed++;
            }
        }

        if ($failed > 0) {
            $this->error("Selesai dengan {$failed} grup gagal. Lihat log untuk detail.");
            return 1;
        }

        $this->info('Selesai. Semua token lama sudah ter-subscribe, sendToTopic() siap dipakai.');
        return 0;
    }
}
