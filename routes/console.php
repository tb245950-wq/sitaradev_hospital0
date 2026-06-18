<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Otomatisasi Backup Database SITARA
 * Berjalan setiap hari pada pukul 00:00
 */
use Illuminate\Support\Facades\Schedule;

Schedule::command('db:backup-daily')->daily();

