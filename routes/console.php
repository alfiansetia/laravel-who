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

// Idempotent: pastikan token lama / yang subscribe-nya gagal diam-diam tetap
// terdaftar ke topic agar sendToTopic() menjangkau semua perangkat.
Schedule::command('app:subscribe-fcm-topic')
    ->dailyAt('05:30');
