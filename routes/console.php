<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote')->hourly();

Schedule::command('app:monitor-do')
    ->everyMinute()
    ->weekdays()
    ->between('06:00', '18:00')
    ->onSuccess(function () {
        // Log::info('Cronjob Monitor DO berhasil dijalankan');
    })->onFailure(function () {
        // Log::error('Cronjob Monitor DO Gagal dijalankan');
    });

Schedule::command('app:odoo-login')
    ->hourly()
    ->weekdays()
    ->between('05:00', '18:00')
    ->onSuccess(function () {
        // Log::info('Cronjob Monitor DO berhasil dijalankan');
    })->onFailure(function () {
        // Log::error('Cronjob Monitor DO Gagal dijalankan');
    });

// Izin Edar: download + auto-import tiap jam 01:00 malam.
// Job jalan di queue (who-worker), scheduler hanya dispatch command singkat.
Schedule::command('sync:izin-edar')
    ->dailyAt('01:00')
    ->withoutOverlapping(120)
    ->onOneServer();

// Idempotent: pastikan token lama / yang subscribe-nya gagal diam-diam tetap
// terdaftar ke topic agar sendToTopic() menjangkau semua perangkat.
Schedule::command('app:subscribe-fcm-topic')
    ->dailyAt('05:30');
