<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Sync feeds every 5 minutes
        $schedule->command('feeds:sync')->everyFiveMinutes();

        // Clean old analytics monthly
        $schedule->command('analytics:clean')->monthly();
        
        // ═══════════════════════════════════════════════════════════
        // GAMIFICATION SCHEDULER (v1.2.0)
        // ═══════════════════════════════════════════════════════════
        
        // Reset weekly points (every Sunday at 00:00)
        $schedule->command('points:reset-weekly')
            ->weekly()
            ->sundays()
            ->at('00:00');
        
        // Reset monthly points (1st of month at 00:00)
        $schedule->command('points:reset-monthly')
            ->monthly()
            ->at('00:00');
        
        // Refresh social tokens (existing)
        if (class_exists('\App\Console\Commands\RefreshSocialTokens')) {
            $schedule->command('tokens:refresh')->daily();
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

