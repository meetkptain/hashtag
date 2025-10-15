<?php

namespace App\Console\Commands;

use App\Services\MediaStorageService;
use Illuminate\Console\Command;

class CleanMediaStorage extends Command
{
    protected $signature = 'media:clean {--days=90}';
    protected $description = 'Clean old cached media files';

    public function handle(MediaStorageService $mediaService): int
    {
        $days = $this->option('days');
        
        $this->info("Cleaning media files older than {$days} days...");
        
        // Afficher la taille actuelle
        $currentSize = $mediaService->getStorageSize();
        $this->line("Current storage: " . $mediaService->formatSize($currentSize));
        
        // Nettoyer
        $deleted = $mediaService->cleanOldMedia($days);
        
        // Afficher la nouvelle taille
        $newSize = $mediaService->getStorageSize();
        $saved = $currentSize - $newSize;
        
        $this->info("✓ Deleted {$deleted} files");
        $this->info("✓ Freed " . $mediaService->formatSize($saved));
        $this->info("✓ New storage: " . $mediaService->formatSize($newSize));
        
        return Command::SUCCESS;
    }
}

