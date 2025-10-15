<?php

namespace App\Http\Controllers;

use App\Models\Feed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class FeedConnectionController extends Controller
{
    /**
     * Initier la connexion Instagram
     */
    public function connectInstagram(Request $request)
    {
        $feedId = $request->get('feed_id');
        
        // Stocker l'ID du feed en session pour le callback
        session(['connecting_feed_id' => $feedId, 'connecting_provider' => 'instagram']);
        
        return Socialite::driver('instagram')
            ->scopes(['instagram_graph_user_profile', 'instagram_graph_user_media'])
            ->redirect();
    }
    
    /**
     * Callback Instagram OAuth
     */
    public function callbackInstagram()
    {
        try {
            $socialUser = Socialite::driver('instagram')->user();
            $feedId = session('connecting_feed_id');
            
            if (!$feedId) {
                return redirect('/feeds')->withErrors(['error' => 'Session expirée']);
            }
            
            // Vérifier que le feed appartient au tenant actuel
            $feed = Feed::find($feedId);
            
            if (!$feed) {
                return redirect('/feeds')->withErrors(['error' => 'Flux non trouvé']);
            }
            
            // Stocker les credentials
            $feed->update([
                'credentials' => [
                    'access_token' => $socialUser->token,
                    'refresh_token' => $socialUser->refreshToken,
                    'user_id' => $socialUser->id,
                    'username' => $socialUser->nickname,
                    'expires_at' => now()->addDays(60)->toDateTimeString(),
                    'connected_at' => now()->toDateTimeString(),
                ]
            ]);
            
            // Nettoyer la session
            session()->forget(['connecting_feed_id', 'connecting_provider']);
            
            return redirect('/feeds')->with('success', 'Instagram connecté avec succès ! 🎉');
            
        } catch (\Exception $e) {
            \Log::error('Instagram OAuth callback error: ' . $e->getMessage());
            return redirect('/feeds')->withErrors(['error' => 'Erreur lors de la connexion Instagram']);
        }
    }
    
    /**
     * Initier la connexion Facebook
     */
    public function connectFacebook(Request $request)
    {
        $feedId = $request->get('feed_id');
        session(['connecting_feed_id' => $feedId, 'connecting_provider' => 'facebook']);
        
        return Socialite::driver('facebook')
            ->scopes(['pages_show_list', 'pages_read_engagement', 'pages_read_user_content'])
            ->redirect();
    }
    
    /**
     * Callback Facebook OAuth
     */
    public function callbackFacebook()
    {
        try {
            $socialUser = Socialite::driver('facebook')->user();
            $feedId = session('connecting_feed_id');
            
            if (!$feedId) {
                return redirect('/feeds')->withErrors(['error' => 'Session expirée']);
            }
            
            $feed = Feed::find($feedId);
            
            if (!$feed) {
                return redirect('/feeds')->withErrors(['error' => 'Flux non trouvé']);
            }
            
            $feed->update([
                'credentials' => [
                    'access_token' => $socialUser->token,
                    'user_id' => $socialUser->id,
                    'name' => $socialUser->name,
                    'expires_at' => now()->addDays(60)->toDateTimeString(),
                    'connected_at' => now()->toDateTimeString(),
                ]
            ]);
            
            session()->forget(['connecting_feed_id', 'connecting_provider']);
            
            return redirect('/feeds')->with('success', 'Facebook connecté avec succès ! 🎉');
            
        } catch (\Exception $e) {
            \Log::error('Facebook OAuth callback error: ' . $e->getMessage());
            return redirect('/feeds')->withErrors(['error' => 'Erreur lors de la connexion Facebook']);
        }
    }
    
    /**
     * Initier la connexion Twitter
     */
    public function connectTwitter(Request $request)
    {
        $feedId = $request->get('feed_id');
        session(['connecting_feed_id' => $feedId, 'connecting_provider' => 'twitter']);
        
        return Socialite::driver('twitter')->redirect();
    }
    
    /**
     * Callback Twitter OAuth
     */
    public function callbackTwitter()
    {
        try {
            $socialUser = Socialite::driver('twitter')->user();
            $feedId = session('connecting_feed_id');
            
            if (!$feedId) {
                return redirect('/feeds')->withErrors(['error' => 'Session expirée']);
            }
            
            $feed = Feed::find($feedId);
            
            if (!$feed) {
                return redirect('/feeds')->withErrors(['error' => 'Flux non trouvé']);
            }
            
            $feed->update([
                'credentials' => [
                    'bearer_token' => $socialUser->token,
                    'user_id' => $socialUser->id,
                    'username' => $socialUser->nickname,
                    'connected_at' => now()->toDateTimeString(),
                ]
            ]);
            
            session()->forget(['connecting_feed_id', 'connecting_provider']);
            
            return redirect('/feeds')->with('success', 'Twitter connecté avec succès ! 🎉');
            
        } catch (\Exception $e) {
            \Log::error('Twitter OAuth callback error: ' . $e->getMessage());
            return redirect('/feeds')->withErrors(['error' => 'Erreur lors de la connexion Twitter']);
        }
    }
    
    /**
     * Déconnecter un compte
     */
    public function disconnect(Feed $feed)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }
        
        $feed->update(['credentials' => null]);
        
        return back()->with('success', 'Compte déconnecté avec succès');
    }
    
    /**
     * Obtenir le status de connexion d'un feed
     */
    public function status(Feed $feed)
    {
        $hasCredentials = !empty($feed->credentials);
        $isExpired = false;
        
        if ($hasCredentials && isset($feed->credentials['expires_at'])) {
            $expiresAt = \Carbon\Carbon::parse($feed->credentials['expires_at']);
            $isExpired = $expiresAt->isPast();
        }
        
        return response()->json([
            'has_credentials' => $hasCredentials,
            'connection_type' => $hasCredentials ? 'advanced' : 'simple',
            'is_expired' => $isExpired,
            'expires_at' => $feed->credentials['expires_at'] ?? null,
            'connected_username' => $feed->credentials['username'] ?? null,
            'days_remaining' => $hasCredentials && !$isExpired 
                ? \Carbon\Carbon::parse($feed->credentials['expires_at'])->diffInDays(now())
                : null,
        ]);
    }
}

