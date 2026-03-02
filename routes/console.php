<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Cron Job: Kirim email followup untuk data request yang terlambat
// Jalankan setiap hari jam 08:00
Schedule::command('audit:send-followup')->dailyAt('08:00');
