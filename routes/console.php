<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// check webhook worker status every minute 
Schedule::exec('/var/www/scripts/control-webhook-worker.sh')
                ->everyMinute()
                // ->withoutOverlapping()
                ->sendOutputTo(storage_path('logs/control-worker.log'))
                ->appendOutputTo(storage_path('logs/control-worker.log'));