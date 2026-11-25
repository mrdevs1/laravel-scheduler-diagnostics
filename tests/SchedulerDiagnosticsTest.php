
<?php

namespace Tests\Feature;

use Tests\TestCase;

class SchedulerDiagnosticsTest extends TestCase
{
    public function test_scheduler_list_command_runs_successfully(): void
    {
        $this->artisan('schedule:list')
            ->assertExitCode(0);
    }

    public function test_scheduler_run_command_runs_successfully(): void
    {
        $this->artisan('schedule:run')
            ->assertExitCode(0);
    }

    public function test_scheduler_environment_is_available(): void
    {
        $this->assertNotEmpty(config('app.timezone'));

        $this->assertNotEmpty(config('cache.default'));
    }
}
