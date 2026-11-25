
<?php

namespace App\Support;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

class SchedulerDiagnostics
{
    public function inspect(): array
    {
        $cacheDriver = Config::get('cache.default');

        return [
            'php_version' => PHP_VERSION,

            'laravel_environment' => app()->environment(),

            'application_timezone' => Config::get('app.timezone'),

            'scheduler_timezone' => Config::get(
                'app.schedule_timezone',
                Config::get('app.timezone')
            ),

            'cache_driver' => $cacheDriver,

            'scheduler_list' => $this->getScheduledTasks(),
        ];
    }

    private function getScheduledTasks(): array
    {
        Artisan::call('schedule:list');

        return [
            'exit_code' => Artisan::output() !== '' ? 0 : null,
            'output' => Artisan::output(),
        ];
    }
}
