<?php

namespace App\Console\Commands;

use App\Services\TokenRefreshService;
use Illuminate\Console\Command;

class RefreshSocialTokens extends Command
{
    protected $signature = 'tokens:refresh {--force}';
    protected $description = 'Refresh expired social media access tokens';

    public function handle(TokenRefreshService $service): int
    {
        $this->info('Checking and refreshing expired tokens...');
        
        $refreshed = $service->refreshAllExpiredTokens();
        
        if ($refreshed > 0) {
            $this->info("✓ {$refreshed} token(s) refreshed successfully");
        } else {
            $this->info('✓ No tokens needed refresh');
        }
        
        return Command::SUCCESS;
    }
}

