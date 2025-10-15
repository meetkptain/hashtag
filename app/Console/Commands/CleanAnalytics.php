<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanAnalytics extends Command
{
    protected $signature = 'analytics:clean {--days=90}';
    protected $description = 'Clean old analytics data';

    public function handle(): int
    {
        $days = $this->option('days');
        $date = now()->subDays($days);

        $this->info("Cleaning analytics older than {$days} days...");

        $tenants = Tenant::where('active', true)->get();

        foreach ($tenants as $tenant) {
            try {
                $tenant->switchDatabase();
                
                $deleted = DB::connection('tenant')
                    ->table('analytics')
                    ->where('created_at', '<', $date)
                    ->delete();

                if ($deleted > 0) {
                    $this->info("✓ Deleted {$deleted} records for tenant: {$tenant->name}");
                }
            } catch (\Exception $e) {
                $this->error("✗ Error cleaning tenant {$tenant->name}: " . $e->getMessage());
            }
        }

        $this->info('Analytics cleanup completed!');
        return Command::SUCCESS;
    }
}

