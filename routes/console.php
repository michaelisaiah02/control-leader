<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote');

// Ngecek yang telat/kecepetan tiap jam 1 pagi
Schedule::command('check:consistency')->dailyAt('00:01');
