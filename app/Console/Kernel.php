<?php

namespace App\Console;

use App\Console\Commands\CheckObsoletenessCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Register the commands for the application.
     *
     * @var array<int, class-string<\Illuminate\Console\Command>>
     */
    protected $commands = [
        CheckObsoletenessCommand::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('obsolescence:check')->daily();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
