<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('sifak:shared:scheduler cleanup-temp-files --days=7')->dailyAt('02:00');
Schedule::command('sifak:shared:scheduler evaluate-alerts')->dailyAt('03:00');
Schedule::command('sifak:m5:recalculate')->dailyAt('04:00');
