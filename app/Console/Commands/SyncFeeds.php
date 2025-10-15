<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\FeedService;
use Illuminate\Console\Command;

class SyncFeeds extends Command
{
    protected $signature = 'feeds:sync {--tenant=}';
    protected $description = 'Synchronize all active feeds';

    public function handle(FeedService $feedService): int
    {
        $tenantFilter = $this->option('tenant');

        $tenants = Tenant::where('active', true)
            ->when($tenantFilter, fn($q) => $q->where('id', $tenantFilter))
            ->get();

        $this->info("Syncing feeds for {$tenants->count()} tenant(s)...");

        foreach ($tenants as $tenant) {
            $this->line("Processing tenant: {$tenant->name}");
            
            try {
                $tenant->switchDatabase();
                $feedService->syncAllFeeds();
                $this->info("✓ Tenant {$tenant->name} synced successfully");
            } catch (\Exception $e) {
                $this->error("✗ Error syncing tenant {$tenant->name}: " . $e->getMessage());
            }
        }

        $this->info('Feed synchronization completed!');
        return Command::SUCCESS;
    }
}

