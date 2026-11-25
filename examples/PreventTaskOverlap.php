
<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('reports:generate')
    ->name('reports:generate')
    ->everyFiveMinutes()
    ->withoutOverlapping(15)
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/reports-scheduler.log'));
