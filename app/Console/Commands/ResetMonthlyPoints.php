<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Gamification\PointsService;
use App\Services\Gamification\LeaderboardService;
use App\Models\Tenant;

class ResetMonthlyPoints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'points:reset-monthly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset monthly points for all tenants (scheduled 1st of month at 00:00)';

    /**
     * Execute the console command.
     */
    public function handle(PointsService $pointsService, LeaderboardService $leaderboardService): int
    {
        $this->info('🔄 Resetting monthly points for all tenants...');

        $tenants = Tenant::all();
        $totalReset = 0;

        foreach ($tenants as $tenant) {
            try {
                $tenant->run(function () use ($pointsService, $leaderboardService, $tenant, &$totalReset) {
                    // Sauvegarder snapshot avant reset (optionnel)
                    $period = now()->subMonth()->format('Y-m'); // Ex: 2025-10
                    $leaderboardService->saveSnapshot('monthly', $period);
                    
                    // Reset points
                    $resetCount = $pointsService->resetMonthlyPoints();
                    $totalReset += $resetCount;
                    
                    $this->info("Tenant {$tenant->id}: Reset {$resetCount} users monthly points");
                });
            } catch (\Exception $e) {
                $this->error("Failed to reset monthly points for tenant {$tenant->id}: " . $e->getMessage());
            }
        }

        $this->info("✅ Total: Reset monthly points for {$totalReset} users across " . $tenants->count() . " tenants");

        return Command::SUCCESS;
    }
}

