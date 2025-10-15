<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Gamification\PointsService;
use App\Services\Gamification\LeaderboardService;
use App\Models\Tenant;
use Stancl\Tenancy\Features\TenantConfig;

class ResetWeeklyPoints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'points:reset-weekly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset weekly points for all tenants (scheduled every Sunday at 00:00)';

    /**
     * Execute the console command.
     */
    public function handle(PointsService $pointsService, LeaderboardService $leaderboardService): int
    {
        $this->info('🔄 Resetting weekly points for all tenants...');

        $tenants = Tenant::all();
        $totalReset = 0;

        foreach ($tenants as $tenant) {
            try {
                $tenant->run(function () use ($pointsService, $leaderboardService, $tenant, &$totalReset) {
                    // Sauvegarder snapshot avant reset (optionnel)
                    $period = now()->format('Y-\WW'); // Ex: 2025-W43
                    $leaderboardService->saveSnapshot('weekly', $period);
                    
                    // Reset points
                    $resetCount = $pointsService->resetWeeklyPoints();
                    $totalReset += $resetCount;
                    
                    $this->info("Tenant {$tenant->id}: Reset {$resetCount} users weekly points");
                });
            } catch (\Exception $e) {
                $this->error("Failed to reset weekly points for tenant {$tenant->id}: " . $e->getMessage());
            }
        }

        $this->info("✅ Total: Reset weekly points for {$totalReset} users across " . $tenants->count() . " tenants");

        return Command::SUCCESS;
    }
}

