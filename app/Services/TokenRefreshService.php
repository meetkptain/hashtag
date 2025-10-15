<?php

namespace App\Services;

use App\Models\Feed;
use App\Models\Tenant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TokenRefreshService
{
    /**
     * Vérifier et rafraîchir tous les tokens expirés
     */
    public function refreshAllExpiredTokens(): int
    {
        $refreshed = 0;
        
        // Parcourir tous les tenants actifs
        $tenants = Tenant::where('active', true)->get();
        
        foreach ($tenants as $tenant) {
            try {
                $tenant->switchDatabase();
                
                // Récupérer les feeds avec credentials
                $feeds = Feed::whereNotNull('credentials')->get();
                
                foreach ($feeds as $feed) {
                    if ($this->shouldRefresh($feed)) {
                        $success = $this->refreshFeedToken($feed);
                        if ($success) {
                            $refreshed++;
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error("Error refreshing tokens for tenant {$tenant->id}: " . $e->getMessage());
            }
        }
        
        return $refreshed;
    }
    
    /**
     * Vérifier si un feed doit être rafraîchi
     */
    protected function shouldRefresh(Feed $feed): bool
    {
        $credentials = $feed->credentials;
        
        // Pas d'expiration définie = pas besoin de refresh
        if (!isset($credentials['expires_at'])) {
            return false;
        }
        
        $expiresAt = \Carbon\Carbon::parse($credentials['expires_at']);
        
        // Rafraîchir 7 jours avant expiration
        return $expiresAt->subDays(7)->isPast();
    }
    
    /**
     * Rafraîchir le token d'un feed
     */
    protected function refreshFeedToken(Feed $feed): bool
    {
        try {
            switch ($feed->type) {
                case 'instagram':
                    return $this->refreshInstagramToken($feed);
                case 'facebook':
                    return $this->refreshFacebookToken($feed);
                // Twitter bearer tokens n'expirent pas
                default:
                    return false;
            }
        } catch (\Exception $e) {
            Log::error("Token refresh failed for feed {$feed->id}: " . $e->getMessage());
            
            // Notifier le tenant que son token a expiré
            $this->notifyTokenExpired($feed);
            
            return false;
        }
    }
    
    /**
     * Rafraîchir token Instagram
     */
    protected function refreshInstagramToken(Feed $feed): bool
    {
        $credentials = $feed->credentials;
        
        if (!isset($credentials['access_token'])) {
            return false;
        }
        
        $response = Http::get('https://graph.instagram.com/refresh_access_token', [
            'grant_type' => 'ig_refresh_token',
            'access_token' => $credentials['access_token'],
        ]);
        
        if (!$response->successful()) {
            return false;
        }
        
        $data = $response->json();
        
        $feed->update([
            'credentials' => array_merge($credentials, [
                'access_token' => $data['access_token'],
                'expires_at' => now()->addSeconds($data['expires_in'])->toDateTimeString(),
                'refreshed_at' => now()->toDateTimeString(),
            ])
        ]);
        
        Log::info("Token refreshed successfully for feed {$feed->id}");
        
        return true;
    }
    
    /**
     * Rafraîchir token Facebook
     */
    protected function refreshFacebookToken(Feed $feed): bool
    {
        // Facebook page tokens peuvent être de longue durée
        // Pour l'instant, retourner false
        return false;
    }
    
    /**
     * Notifier le tenant d'un token expiré
     */
    protected function notifyTokenExpired(Feed $feed)
    {
        // TODO: Envoyer email au tenant
        // TODO: Créer notification dans le dashboard
        Log::warning("Token expired for feed {$feed->id}, tenant should reconnect");
    }
}

