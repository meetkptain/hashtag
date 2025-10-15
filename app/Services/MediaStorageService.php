<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MediaStorageService
{
    protected string $disk = 'public'; // Local par défaut
    protected string $basePath = 'media/posts';

    public function __construct()
    {
        // Optionnel : Utiliser le disque configuré dans .env si défini
        $this->disk = config('filesystems.default', 'public');
    }

    /**
     * Télécharger et stocker une image
     * 
     * @param string $url URL de l'image source
     * @param string $postId ID du post
     * @return string|null URL locale de l'image ou null si échec
     */
    public function downloadAndStore(string $url, string $postId): ?string
    {
        try {
            // Télécharger l'image
            $response = Http::timeout(10)->get($url);
            
            if (!$response->successful()) {
                return null;
            }

            // Générer un nom de fichier unique
            $extension = $this->getExtensionFromUrl($url);
            $filename = $postId . '_' . Str::random(10) . '.' . $extension;
            $path = $this->basePath . '/' . $filename;

            // Stocker l'image
            Storage::disk($this->disk)->put($path, $response->body());

            // Retourner l'URL publique
            return Storage::disk($this->disk)->url($path);

        } catch (\Exception $e) {
            \Log::error("Failed to download media: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Télécharger plusieurs médias
     */
    public function downloadMultiple(array $mediaItems, string $postId): array
    {
        $downloaded = [];

        foreach ($mediaItems as $media) {
            $localUrl = $this->downloadAndStore($media['url'], $postId);
            
            $downloaded[] = [
                'type' => $media['type'],
                'url' => $localUrl ?? $media['url'], // Fallback sur l'URL originale
                'original_url' => $media['url'],
                'cached' => $localUrl !== null,
            ];
        }

        return $downloaded;
    }

    /**
     * Supprimer un média
     */
    public function delete(string $path): bool
    {
        try {
            return Storage::disk($this->disk)->delete($path);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Nettoyer les médias anciens (> 90 jours)
     */
    public function cleanOldMedia(int $days = 90): int
    {
        $deleted = 0;
        $date = now()->subDays($days);

        $files = Storage::disk($this->disk)->allFiles($this->basePath);

        foreach ($files as $file) {
            $lastModified = Storage::disk($this->disk)->lastModified($file);
            
            if ($lastModified < $date->timestamp) {
                Storage::disk($this->disk)->delete($file);
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Obtenir l'extension depuis l'URL
     */
    protected function getExtensionFromUrl(string $url): string
    {
        $parsed = parse_url($url);
        $path = $parsed['path'] ?? '';
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        // Extensions valides pour images
        $validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array(strtolower($extension), $validExtensions)) {
            return strtolower($extension);
        }

        // Par défaut
        return 'jpg';
    }

    /**
     * Obtenir la taille de stockage utilisée
     */
    public function getStorageSize(): int
    {
        $files = Storage::disk($this->disk)->allFiles($this->basePath);
        $totalSize = 0;

        foreach ($files as $file) {
            $totalSize += Storage::disk($this->disk)->size($file);
        }

        return $totalSize; // en bytes
    }

    /**
     * Formater la taille en format lisible
     */
    public function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}

