<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwalkan penarikan data log ZKTeco setiap menit.
// Logika interval masing-masing mesin (misal: 5 menit atau 10 menit)
// akan di-handle di dalam command `zkteco:pull`.
Schedule::command('zkteco:pull')->everyMinute();
