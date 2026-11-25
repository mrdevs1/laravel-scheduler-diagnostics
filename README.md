# Laravel Scheduler Diagnostics

A practical guide to diagnosing and troubleshooting Laravel Task Scheduler issues in development and production environments.

## Overview

Laravel Task Scheduler allows developers to manage scheduled tasks from a single `routes/console.php` file instead of configuring multiple cron entries.

However, scheduled tasks can fail for several reasons, including incorrect cron configuration, timezone mismatches, overlapping executions, server permissions, cache locks, and background process failures.

This repository provides practical examples and troubleshooting techniques for identifying and resolving common Laravel Scheduler problems.

## Table of Contents

- [How Laravel Scheduler Works](#how-laravel-scheduler-works)
- [Server Cron Configuration](#server-cron-configuration)
- [Common Scheduler Problems](#common-scheduler-problems)
- [Task Frequency and Timing](#task-frequency-and-timing)
- [Preventing Task Overlaps](#preventing-task-overlaps)
- [Running Tasks on Multiple Servers](#running-tasks-on-multiple-servers)
- [Timezone and DST Issues](#timezone-and-dst-issues)
- [Background Task Execution](#background-task-execution)
- [Scheduled Queue Jobs](#scheduled-queue-jobs)
- [Maintenance Mode](#maintenance-mode)
- [Stuck Tasks and Cache Locks](#stuck-tasks-and-cache-locks)
- [Logging and Monitoring](#logging-and-monitoring)
- [Useful Artisan Commands](#useful-artisan-commands)
- [Production Troubleshooting Checklist](#production-troubleshooting-checklist)
- [Contributing](#contributing)

## How Laravel Scheduler Works

Laravel Scheduler uses a single cron entry to execute scheduled tasks.

The server cron runs Laravel's scheduler every minute. Laravel then checks which tasks should execute based on their configured frequency.

Example:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('reports:generate')
    ->daily();
```

The scheduler evaluates the task and runs it when its scheduled time arrives.

## Server Cron Configuration

Add the following cron entry to your server:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

Replace `/path-to-your-project` with your actual Laravel project directory.

Common cron configuration problems:

- Incorrect project directory.
- Incorrect PHP executable path.
- Missing file permissions.
- Cron running under the wrong system user.
- Environment variables not loading correctly.
- Server cron service not running.

Check cron service:

```bash
sudo systemctl status cron
```

On some distributions, the service name is `crond`.

## Common Scheduler Problems

### Scheduled Tasks Are Not Running

Check the following:

1. Confirm that the cron entry exists.
2. Run `php artisan schedule:run` manually.
3. Check the PHP version used by cron.
4. Verify the Laravel environment configuration.
5. Review application and server logs.

### Tasks Run at the Wrong Time

Possible causes:

- Incorrect application timezone.
- Server timezone mismatch.
- Incorrect scheduling frequency.
- Daylight Saving Time changes.

### Tasks Run More Than Once

Possible causes:

- Multiple cron entries.
- Multiple application servers.
- Missing overlap protection.
- Incorrect deployment configuration.

## Task Frequency and Timing

Laravel provides several scheduling methods.

```php
Schedule::command('reports:generate')->everyMinute();

Schedule::command('reports:generate')->hourly();

Schedule::command('reports:generate')->daily();

Schedule::command('reports:generate')->weekly();

Schedule::command('reports:generate')->monthly();
```

You can also define specific execution times:

```php
Schedule::command('reports:generate')
    ->dailyAt('02:30');
```

For custom intervals:

```php
Schedule::command('reports:generate')
    ->everyFiveMinutes();
```

## Preventing Task Overlaps

Long-running tasks may overlap if the previous execution has not finished before the next scheduled execution begins.

Use `withoutOverlapping()`:

```php
Schedule::command('reports:generate')
    ->everyMinute()
    ->withoutOverlapping();
```

You can specify the lock expiration time:

```php
Schedule::command('reports:generate')
    ->withoutOverlapping(30);
```

The value represents the lock expiration time in minutes.

For additional details, see:

`examples/PreventTaskOverlap.php`

## Running Tasks on Multiple Servers

When Laravel runs on multiple application servers, the same scheduled task may execute on each server.

Use `onOneServer()` to ensure that only one server runs the task:

```php
Schedule::command('reports:generate')
    ->daily()
    ->onOneServer();
```

This requires a shared cache backend that supports atomic locks.

## Timezone and DST Issues

Laravel supports timezone-specific scheduling.

```php
Schedule::command('reports:generate')
    ->dailyAt('02:00')
    ->timezone('Asia/Dhaka');
```

Be careful with Daylight Saving Time.

Some scheduled tasks may execute twice or not execute at all when clocks change. Avoid relying on DST-sensitive execution times for critical operations.

## Background Task Execution

By default, scheduled commands execute in the foreground.

For commands that should run in the background:

```php
Schedule::command('reports:generate')
    ->daily()
    ->runInBackground();
```

Use background execution carefully and monitor the process output.

## Scheduled Queue Jobs

Laravel allows scheduled execution of queued jobs.

```php
Schedule::job(new GenerateReport)
    ->daily();
```

Make sure your queue worker is running.

For example:

```bash
php artisan queue:work
```

A scheduled queue job being dispatched does not guarantee that it has already been processed.

## Maintenance Mode

Laravel normally does not execute scheduled tasks while the application is in maintenance mode.

You can allow a task to run during maintenance:

```php
Schedule::command('reports:generate')
    ->daily()
    ->evenInMaintenanceMode();
```

Use this option only for tasks that are safe to execute during maintenance.

## Stuck Tasks and Cache Locks

Tasks using `withoutOverlapping()` depend on cache locks.

If a process crashes unexpectedly, a lock may remain until its expiration time.

Clear scheduler locks using:

```bash
php artisan schedule:clear-cache
```

Do not clear scheduler locks without checking whether an important task is still running.

## Logging and Monitoring

Use Laravel's scheduling output methods to monitor task execution.

```php
Schedule::command('reports:generate')
    ->daily()
    ->appendOutputTo(storage_path('logs/scheduler.log'));
```

You can also use:

```php
Schedule::command('reports:generate')
    ->daily()
    ->sendOutputTo(storage_path('logs/report.log'));
```

For failure notifications:

```php
Schedule::command('reports:generate')
    ->daily()
    ->onFailure(function () {
        logger()->error('Scheduled report generation failed.');
    });
```

Monitor:

- Task execution time.
- Failed scheduled commands.
- Background process failures.
- Queue worker availability.
- Cache lock status.
- Server cron execution.

## Useful Artisan Commands

| Command | Purpose |
|---|---|
| `php artisan schedule:list` | List scheduled tasks |
| `php artisan schedule:run` | Execute due scheduled tasks |
| `php artisan schedule:work` | Run the scheduler continuously |
| `php artisan schedule:test` | Test a scheduled event interactively |
| `php artisan schedule:clear-cache` | Clear scheduler cache locks |
| `php artisan queue:work` | Process queued jobs |

Some commands may vary depending on your Laravel version.

## Production Troubleshooting Checklist

Before deploying scheduled tasks to production, verify the following:

- [ ] Server cron is configured.
- [ ] Cron uses the correct PHP executable.
- [ ] Laravel application path is correct.
- [ ] File permissions are configured.
- [ ] Application timezone is correct.
- [ ] Scheduled task frequency is verified.
- [ ] Long-running tasks have overlap protection.
- [ ] Multi-server tasks use `onOneServer()` when required.
- [ ] Shared cache supports atomic locks.
- [ ] Queue workers are running.
- [ ] Task failures are logged.
- [ ] Scheduler output is monitored.
- [ ] Maintenance mode behavior is tested.

## Contributing

Contributions are welcome.

If you discover a scheduler issue or have a useful troubleshooting technique, feel free to open an issue or submit a pull request.

Please include:

- Laravel version.
- PHP version.
- Server environment.
- Steps to reproduce the issue.
- Expected and actual behavior.

## License

This project is open-source and available under the MIT License.
