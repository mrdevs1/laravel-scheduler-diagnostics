
<?php

use Illuminate\Support\Facades\Schedule;

// Run every minute
Schedule::command('reports:generate')
    ->everyMinute();

// Run daily at 2 AM
Schedule::command('reports:daily')
    ->dailyAt('02:00')
    ->timezone('Asia/Dhaka');

// Run weekly
Schedule::command('reports:weekly')
    ->weekly();

// Run in the background
Schedule::command('reports:analytics')
    ->daily()
    ->runInBackground();

// Run during maintenance mode
Schedule::command('reports:cleanup')
    ->daily()
    ->evenInMaintenanceMode();

// Dispatch a queued job
// Schedule::job(new \App\Jobs\GenerateReport)->daily();
