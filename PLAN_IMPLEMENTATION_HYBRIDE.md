# 🚀 Plan Complet d'Implémentation - Solution Hybride

## 🎯 **OBJECTIF**

Permettre aux clients de choisir entre :
- **Mode Simple** (29-79€/mois) : API HashMyTag, hashtags publics
- **Mode Avancé** (+20€/mois) : Connexion leur compte, limites dédiées

---

## 📋 **VUE D'ENSEMBLE**

### **Phases du Projet**

```
Phase 1 : Backend Core (8h)
  → Logique hybride providers
  → Passage credentials
  → Tests unitaires

Phase 2 : OAuth Feed Connection (6h)
  → Contrôleur OAuth feeds
  → Routes callbacks
  → Token management

Phase 3 : Frontend UI (4h)
  → Interface mode simple/avancé
  → Boutons connexion
  → Status affichage

Phase 4 : Token Refresh (2h)
  → Service refresh automatique
  → Commande Artisan
  → Scheduler

Phase 5 : Tests & QA (4h)
  → Tests d'intégration
  → Tests E2E
  → Validation

Phase 6 : Documentation (2h)
  → Guide utilisateur
  → Guide admin
  → FAQ

TOTAL : 26 heures = 3-4 jours
```

---

## 📅 **PHASE 1 : BACKEND CORE (8h)**

### **Objectif**
Permettre aux providers d'utiliser soit credentials globaux, soit credentials du feed.

---

### **Tâche 1.1 : Modifier InstagramFeed (1h30)**

**Fichier :** `app/Services/Feeds/InstagramFeed.php`

**Modifications :**

```php
// LIGNE 10-17 - REMPLACER
class InstagramFeed implements FeedProvider
{
    protected string $accessToken;
    protected string $apiUrl = 'https://graph.instagram.com/v18.0';

    public function __construct()
    {
        $this->accessToken = config('feeds.credentials.instagram.access_token');
    }
```

**PAR :**

```php
class InstagramFeed implements FeedProvider
{
    protected string $accessToken;
    protected string $userId;
    protected string $apiUrl = 'https://graph.instagram.com/v18.0';

    public function __construct(?array $credentials = null)
    {
        // Priorité : credentials fournis > config globale
        if ($credentials && isset($credentials['access_token'])) {
            $this->accessToken = $credentials['access_token'];
            $this->userId = $credentials['user_id'] ?? config('feeds.credentials.instagram.user_id');
        } else {
            $this->accessToken = config('feeds.credentials.instagram.access_token');
            $this->userId = config('feeds.credentials.instagram.user_id');
        }
    }
    
    /**
     * Définir des credentials personnalisés (méthode alternative)
     */
    public function setCredentials(array $credentials): self
    {
        $this->accessToken = $credentials['access_token'] ?? $this->accessToken;
        $this->userId = $credentials['user_id'] ?? $this->userId;
        return $this;
    }
    
    /**
     * Obtenir le token actuel (pour debug)
     */
    public function getToken(): string
    {
        return $this->accessToken;
    }
}
```

**Tests à ajouter :**
```php
// tests/Unit/InstagramFeedTest.php
public function test_uses_custom_credentials_when_provided()
{
    $feed = new InstagramFeed(['access_token' => 'custom_token']);
    $this->assertEquals('custom_token', $feed->getToken());
}

public function test_falls_back_to_global_credentials()
{
    $feed = new InstagramFeed();
    $this->assertEquals(config('feeds.credentials.instagram.access_token'), $feed->getToken());
}
```

---

### **Tâche 1.2 : Modifier FacebookFeed (1h30)**

**Fichier :** `app/Services/Feeds/FacebookFeed.php`

**Modifications identiques à Instagram :**

```php
// LIGNE 10-17 - REMPLACER
public function __construct()
{
    $this->accessToken = config('feeds.credentials.facebook.access_token');
}

// PAR
public function __construct(?array $credentials = null)
{
    if ($credentials && isset($credentials['access_token'])) {
        $this->accessToken = $credentials['access_token'];
    } else {
        $this->accessToken = config('feeds.credentials.facebook.access_token');
    }
}

public function setCredentials(array $credentials): self
{
    $this->accessToken = $credentials['access_token'] ?? $this->accessToken;
    return $this;
}

public function getToken(): string
{
    return $this->accessToken;
}
```

---

### **Tâche 1.3 : Modifier TwitterFeed (1h30)**

**Fichier :** `app/Services/Feeds/TwitterFeed.php`

**Modifications identiques :**

```php
public function __construct(?array $credentials = null)
{
    if ($credentials && isset($credentials['bearer_token'])) {
        $this->bearerToken = $credentials['bearer_token'];
    } else {
        $this->bearerToken = config('feeds.credentials.twitter.bearer_token');
    }
}

public function setCredentials(array $credentials): self
{
    $this->bearerToken = $credentials['bearer_token'] ?? $this->bearerToken;
    return $this;
}
```

---

### **Tâche 1.4 : Modifier FeedService (2h)**

**Fichier :** `app/Services/FeedService.php`

**Modification 1 - Ligne 29-40 :**

```php
// REMPLACER
public function syncFeed(Feed $feed): int
{
    try {
        $provider = $this->getProvider($feed->type);
        
        if (!$provider) {
            Log::error("Provider not found for feed type: {$feed->type}");
            return 0;
        }

        // Fetch posts from provider
        $posts = $provider->fetch($feed->config);

// PAR
public function syncFeed(Feed $feed): int
{
    try {
        // ✅ Passer les credentials du feed au provider
        $provider = $this->getProvider($feed->type, $feed->credentials);
        
        if (!$provider) {
            Log::error("Provider not found for feed type: {$feed->type}");
            return 0;
        }

        // Fetch posts from provider
        $posts = $provider->fetch($feed->config);
```

**Modification 2 - Ligne 111-121 :**

```php
// REMPLACER
protected function getProvider(string $type): ?\App\Contracts\FeedProvider
{
    $providers = config('feeds.providers');
    $providerClass = $providers[$type] ?? null;

    if (!$providerClass || !class_exists($providerClass)) {
        return null;
    }

    return app($providerClass);
}

// PAR
protected function getProvider(string $type, ?array $credentials = null): ?\App\Contracts\FeedProvider
{
    $providers = config('feeds.providers');
    $providerClass = $providers[$type] ?? null;

    if (!$providerClass || !class_exists($providerClass)) {
        return null;
    }

    // ✅ Instancier avec credentials si fournis
    if ($credentials) {
        return new $providerClass($credentials);
    }

    return app($providerClass);
}
```

**Ajouter nouvelle méthode :**

```php
/**
 * Obtenir le type de connexion d'un feed
 */
public function getConnectionType(Feed $feed): string
{
    return !empty($feed->credentials) ? 'advanced' : 'simple';
}

/**
 * Vérifier si un feed utilise des credentials personnalisés
 */
public function hasCustomCredentials(Feed $feed): bool
{
    return !empty($feed->credentials) && isset($feed->credentials['access_token']);
}
```

---

### **Tâche 1.5 : Tests Unitaires Backend (1h30)**

**Nouveau fichier :** `tests/Unit/HybridFeedTest.php`

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Feeds\InstagramFeed;
use App\Services\FeedService;
use App\Models\Feed;

class HybridFeedTest extends TestCase
{
    /** @test */
    public function provider_uses_custom_credentials_when_provided()
    {
        $credentials = ['access_token' => 'custom_token_123'];
        $provider = new InstagramFeed($credentials);
        
        $this->assertEquals('custom_token_123', $provider->getToken());
    }
    
    /** @test */
    public function provider_falls_back_to_global_credentials()
    {
        config(['feeds.credentials.instagram.access_token' => 'global_token']);
        $provider = new InstagramFeed();
        
        $this->assertEquals('global_token', $provider->getToken());
    }
    
    /** @test */
    public function feed_service_passes_credentials_to_provider()
    {
        $feed = Feed::factory()->create([
            'type' => 'instagram',
            'credentials' => ['access_token' => 'feed_token']
        ]);
        
        $service = app(FeedService::class);
        $this->assertTrue($service->hasCustomCredentials($feed));
        $this->assertEquals('advanced', $service->getConnectionType($feed));
    }
    
    /** @test */
    public function feed_without_credentials_uses_simple_mode()
    {
        $feed = Feed::factory()->create([
            'type' => 'instagram',
            'credentials' => null
        ]);
        
        $service = app(FeedService::class);
        $this->assertFalse($service->hasCustomCredentials($feed));
        $this->assertEquals('simple', $service->getConnectionType($feed));
    }
}
```

---

## 📅 **PHASE 2 : OAUTH FEED CONNECTION (6h)**

### **Objectif**
Permettre aux clients de connecter leurs comptes via OAuth.

---

### **Tâche 2.1 : Créer FeedConnectionController (3h)**

**Nouveau fichier :** `app/Http/Controllers/FeedConnectionController.php`

```php
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
        
        return Socialite::driver('twitter')
            ->redirect();
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
        // Vérifier les permissions
        if (!Auth::check()) {
            return redirect('/login');
        }
        
        // Supprimer les credentials
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
```

---

### **Tâche 2.2 : Ajouter Routes (30 min)**

**Fichier :** `routes/web.php`

**Ajouter après ligne 57 :**

```php
use App\Http\Controllers\FeedConnectionController;

// Feed Social Account Connection (dans le groupe auth)
Route::middleware(['auth:sanctum'])->group(function () {
    // ... routes existantes
    
    // Routes de connexion des comptes sociaux pour feeds
    Route::prefix('connect')->group(function () {
        // Instagram
        Route::get('/instagram', [FeedConnectionController::class, 'connectInstagram'])
            ->name('feed.connect.instagram');
        Route::get('/instagram/callback', [FeedConnectionController::class, 'callbackInstagram'])
            ->name('feed.connect.instagram.callback');
        
        // Facebook
        Route::get('/facebook', [FeedConnectionController::class, 'connectFacebook'])
            ->name('feed.connect.facebook');
        Route::get('/facebook/callback', [FeedConnectionController::class, 'callbackFacebook'])
            ->name('feed.connect.facebook.callback');
        
        // Twitter
        Route::get('/twitter', [FeedConnectionController::class, 'connectTwitter'])
            ->name('feed.connect.twitter');
        Route::get('/twitter/callback', [FeedConnectionController::class, 'callbackTwitter'])
            ->name('feed.connect.twitter.callback');
    });
    
    // Disconnect & status
    Route::post('/feeds/{feed}/disconnect', [FeedConnectionController::class, 'disconnect'])
        ->name('feed.disconnect');
    Route::get('/feeds/{feed}/connection-status', [FeedConnectionController::class, 'status'])
        ->name('feed.connection.status');
});
```

---

### **Tâche 2.3 : Ajouter Route API (15 min)**

**Fichier :** `routes/api.php`

**Ajouter dans le groupe auth:sanctum :**

```php
// Feed connection management
Route::get('feeds/{feed}/connection-status', [App\Http\Controllers\FeedConnectionController::class, 'status']);
```

---

## 📅 **PHASE 3 : FRONTEND UI (4h)**

### **Tâche 3.1 : Créer Composant Connection Modal (2h)**

**Nouveau fichier :** `resources/js/Components/FeedConnectionModal.vue`

```vue
<template>
  <Modal :show="show" @close="$emit('close')">
    <div class="p-6">
      <h2 class="text-2xl font-bold mb-6">Mode de Connexion</h2>
      
      <div class="space-y-4">
        <!-- Mode Simple -->
        <label 
          :class="[
            'border-2 rounded-lg p-4 cursor-pointer transition',
            connectionMode === 'simple' 
              ? 'border-primary-500 bg-primary-50' 
              : 'border-gray-300 hover:border-gray-400'
          ]"
        >
          <div class="flex items-start gap-3">
            <input 
              type="radio" 
              v-model="connectionMode" 
              value="simple"
              class="mt-1"
            >
            <div class="flex-1">
              <div class="flex items-center gap-2">
                <span class="font-semibold text-lg">Mode Simple</span>
                <span class="badge badge-success">Inclus</span>
              </div>
              <p class="text-sm text-gray-600 mt-1">
                Affiche les posts publics avec vos hashtags. Utilise l'API HashMyTag.
              </p>
              <ul class="text-sm text-gray-500 mt-2 space-y-1">
                <li>✅ Configuration en 2 minutes</li>
                <li>✅ Pas de compte développeur nécessaire</li>
                <li>✅ Posts publics avec hashtags</li>
                <li>⚠️ Limites API partagées</li>
              </ul>
            </div>
          </div>
        </label>
        
        <!-- Mode Avancé -->
        <label 
          :class="[
            'border-2 rounded-lg p-4 cursor-pointer transition',
            connectionMode === 'advanced' 
              ? 'border-purple-500 bg-purple-50' 
              : 'border-gray-300 hover:border-gray-400'
          ]"
        >
          <div class="flex items-start gap-3">
            <input 
              type="radio" 
              v-model="connectionMode" 
              value="advanced"
              class="mt-1"
            >
            <div class="flex-1">
              <div class="flex items-center gap-2">
                <span class="font-semibold text-lg">Mode Avancé</span>
                <span class="badge badge-warning">+20€/mois</span>
              </div>
              <p class="text-sm text-gray-600 mt-1">
                Connectez votre compte pour un accès complet. Limites API dédiées.
              </p>
              <ul class="text-sm text-gray-500 mt-2 space-y-1">
                <li>✅ Tous vos posts (même privés)</li>
                <li>✅ Stories, mentions, tags</li>
                <li>✅ Limites API dédiées (200/h)</li>
                <li>✅ Aucune limite partagée</li>
              </ul>
            </div>
          </div>
        </label>
      </div>
      
      <!-- Actions selon le mode -->
      <div class="mt-6">
        <div v-if="connectionMode === 'simple'">
          <button @click="saveSimpleMode" class="btn btn-primary w-full">
            Continuer avec Mode Simple
          </button>
        </div>
        
        <div v-if="connectionMode === 'advanced'">
          <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
            <p class="text-sm text-yellow-800">
              <strong>Note :</strong> Le mode avancé coûte 20€/mois supplémentaires par compte connecté.
            </p>
          </div>
          
          <button 
            @click="confirmAdvanced" 
            class="btn btn-primary w-full"
            :disabled="!canUseAdvanced"
          >
            <span v-if="canUseAdvanced">
              Continuer avec Mode Avancé (+20€/mois)
            </span>
            <span v-else>
              Upgrade requis pour Mode Avancé
            </span>
          </button>
        </div>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { ref, computed } from 'vue';
import Modal from './Modal.vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
  show: Boolean,
  feed: Object,
  tenant: Object,
});

const emit = defineEmits(['close', 'modeSelected']);

const page = usePage();
const connectionMode = ref('simple');

// Vérifier si le client peut utiliser le mode avancé
const canUseAdvanced = computed(() => {
  const plan = page.props.tenant?.plan || 'starter';
  // Starter ne peut pas, Business+Enterprise peuvent
  return ['business', 'enterprise'].includes(plan);
});

const saveSimpleMode = () => {
  emit('modeSelected', { mode: 'simple', credentials: null });
};

const confirmAdvanced = () => {
  if (!canUseAdvanced.value) {
    alert('Le mode avancé nécessite un plan Business ou Enterprise');
    return;
  }
  emit('modeSelected', { mode: 'advanced', credentials: null });
};
</script>
```

---

### **Tâche 3.2 : Modifier Feeds.vue (2h)**

**Fichier :** `resources/js/Pages/Dashboard/Feeds.vue`

**Ajouts nécessaires :**

```vue
<script setup>
// AJOUTER ces imports
import FeedConnectionModal from '../../Components/FeedConnectionModal.vue';

// AJOUTER ces refs
const showConnectionModal = ref(false);
const connectingFeed = ref(null);

// AJOUTER cette méthode
const showConnectionOptions = (feed = null) => {
  connectingFeed.value = feed;
  showConnectionModal.value = true;
};

// AJOUTER cette méthode
const handleModeSelected = ({ mode, credentials }) => {
  showConnectionModal.value = false;
  
  if (mode === 'simple') {
    // Continuer avec le formulaire normal
    showAddModal.value = true;
  } else {
    // Rediriger vers OAuth
    const provider = form.value.type; // instagram, facebook, twitter
    window.location.href = `/connect/${provider}?feed_id=${connectingFeed.value?.id || 'new'}`;
  }
};

// MODIFIER la méthode saveFeed
const saveFeed = async () => {
  // Si nouveau flux, demander d'abord le mode
  if (!editingFeed.value) {
    showConnectionOptions();
    return;
  }
  
  // Si flux existant, sauvegarder directement
  try {
    await axios.put(`/api/feeds/${editingFeed.value.id}`, form.value);
    showAddModal.value = false;
    loadFeeds();
  } catch (error) {
    alert('Erreur lors de la sauvegarde');
  }
};
</script>

<template>
  <!-- AJOUTER après le Modal existant -->
  
  <!-- Connection Mode Modal -->
  <FeedConnectionModal 
    :show="showConnectionModal" 
    :feed="connectingFeed"
    :tenant="$page.props.tenant"
    @close="showConnectionModal = false"
    @modeSelected="handleModeSelected"
  />
  
  <!-- MODIFIER les cartes de flux pour afficher le status -->
  <div v-for="feed in feeds" :key="feed.id" class="card">
    <!-- Contenu existant... -->
    
    <!-- AJOUTER indicateur de connexion -->
    <div v-if="feed.credentials" class="mt-3 flex items-center gap-2 text-sm">
      <span class="badge badge-info">Mode Avancé</span>
      <span class="text-gray-600">
        Compte connecté : @{{ feed.credentials.username }}
      </span>
    </div>
    
    <div v-else class="mt-3">
      <span class="badge badge-success">Mode Simple</span>
    </div>
    
    <!-- MODIFIER les actions pour ajouter déconnexion -->
    <div class="flex gap-2 mt-4">
      <button @click="syncFeed(feed)" class="btn btn-secondary flex-1 text-sm">
        🔄 Synchroniser
      </button>
      
      <!-- AJOUTER si mode avancé -->
      <button 
        v-if="feed.credentials" 
        @click="disconnectFeed(feed)" 
        class="btn btn-secondary text-sm"
      >
        🔌 Déconnecter
      </button>
      
      <button @click="editFeed(feed)" class="btn btn-secondary flex-1 text-sm">
        ✏️ Modifier
      </button>
      <button @click="deleteFeed(feed)" class="btn btn-danger text-sm">
        🗑️
      </button>
    </div>
  </div>
</template>
```

---

## 📅 **PHASE 4 : TOKEN REFRESH SERVICE (2h)**

### **Tâche 4.1 : Créer TokenRefreshService (1h)**

**Nouveau fichier :** `app/Services/TokenRefreshService.php`

```php
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
        // Logique similaire à Instagram si nécessaire
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
```

---

### **Tâche 4.2 : Créer Commande Refresh (30 min)**

**Nouveau fichier :** `app/Console/Commands/RefreshSocialTokens.php`

```php
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
```

---

### **Tâche 4.3 : Ajouter au Scheduler (15 min)**

**Fichier :** `routes/console.php`

**Ajouter :**

```php
use Illuminate\Support\Facades\Schedule;

// Schedule feed synchronization every 5 minutes
Schedule::command('feeds:sync')->everyFiveMinutes();

// Clean old analytics data monthly
Schedule::command('analytics:clean')->monthly();

// ✅ NOUVEAU : Refresh social tokens daily
Schedule::command('tokens:refresh')->daily();
```

---

## 📅 **PHASE 5 : PRICING & BILLING (3h)**

### **Tâche 5.1 : Modifier config/plans.php (30 min)**

**Fichier :** `config/plans.php`

**Ajouter dans les add-ons :**

```php
'addons' => [
    'extra_feed' => [
        'name' => 'Flux supplémentaire',
        'price' => 15,
        'stripe_price' => env('STRIPE_PRICE_EXTRA_FEED'),
    ],
    'extra_hashtags' => [
        'name' => '5 hashtags supplémentaires',
        'price' => 10,
        'stripe_price' => env('STRIPE_PRICE_EXTRA_HASHTAGS'),
    ],
    
    // ✅ NOUVEAU
    'instagram_connection' => [
        'name' => 'Connexion compte Instagram (Mode Avancé)',
        'price' => 20,
        'stripe_price' => env('STRIPE_PRICE_INSTAGRAM_CONNECTION'),
        'description' => 'Connectez votre compte Instagram pour un accès complet',
        'features' => [
            'Tous vos posts (privés inclus)',
            'Stories, mentions, tags',
            'Limites API dédiées (200/h)',
            'Aucune limite partagée',
        ]
    ],
    
    'facebook_connection' => [
        'name' => 'Connexion compte Facebook (Mode Avancé)',
        'price' => 20,
        'stripe_price' => env('STRIPE_PRICE_FACEBOOK_CONNECTION'),
    ],
    
    'twitter_connection' => [
        'name' => 'Connexion compte Twitter (Mode Avancé)',
        'price' => 20,
        'stripe_price' => env('STRIPE_PRICE_TWITTER_CONNECTION'),
    ],
    
    'premium_support' => [
        'name' => 'Support prioritaire',
        'price' => 50,
        'stripe_price' => env('STRIPE_PRICE_PREMIUM_SUPPORT'),
    ],
],
```

---

### **Tâche 5.2 : Créer Migration Add-ons (30 min)**

**Nouveau fichier :** `database/migrations/tenant/2024_01_01_000005_create_tenant_addons_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_addons', function (Blueprint $table) {
            $table->id();
            $table->string('addon_key'); // instagram_connection, facebook_connection
            $table->boolean('active')->default(true);
            $table->json('metadata')->nullable(); // infos supplémentaires
            $table->timestamp('activated_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->index('addon_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_addons');
    }
};
```

---

### **Tâche 5.3 : Créer Modèle TenantAddon (30 min)**

**Nouveau fichier :** `app/Models/TenantAddon.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantAddon extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'addon_key',
        'active',
        'metadata',
        'activated_at',
        'expires_at',
    ];

    protected $casts = [
        'active' => 'boolean',
        'metadata' => 'array',
        'activated_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Vérifier si l'addon est actif et valide
     */
    public function isValid(): bool
    {
        if (!$this->active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }
}
```

---

### **Tâche 5.4 : Ajouter Méthodes dans Tenant (30 min)**

**Fichier :** `app/Models/Tenant.php`

**Ajouter ces méthodes :**

```php
/**
 * Vérifier si un addon est actif
 */
public function hasAddon(string $addonKey): bool
{
    $this->switchDatabase();
    
    $addon = \App\Models\TenantAddon::where('addon_key', $addonKey)
        ->where('active', true)
        ->first();
    
    return $addon ? $addon->isValid() : false;
}

/**
 * Activer un addon
 */
public function activateAddon(string $addonKey, ?array $metadata = null): bool
{
    $this->switchDatabase();
    
    \App\Models\TenantAddon::create([
        'addon_key' => $addonKey,
        'active' => true,
        'metadata' => $metadata,
        'activated_at' => now(),
    ]);
    
    return true;
}

/**
 * Vérifier si peut utiliser mode avancé
 */
public function canUseAdvancedMode(string $provider): bool
{
    // Plan Enterprise = inclus
    if ($this->plan === 'enterprise') {
        return true;
    }
    
    // Plans inférieurs = vérifier addon
    $addonKey = "{$provider}_connection";
    return $this->hasAddon($addonKey);
}
```

---

### **Tâche 5.5 : Modifier Page Billing (1h)**

**Fichier :** `resources/js/Pages/Dashboard/Billing.vue`

**Ajouter section Add-ons :**

```vue
<template>
  <!-- Après la section plans existante -->
  
  <!-- Section Add-ons Premium -->
  <div class="mt-8">
    <h3 class="text-2xl font-bold mb-6">🚀 Add-ons Premium</h3>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Add-on Instagram Connection -->
      <div class="card">
        <div class="flex items-center gap-3 mb-4">
          <span class="text-4xl">📷</span>
          <div>
            <h4 class="font-bold text-lg">Instagram Avancé</h4>
            <p class="text-sm text-gray-600">Mode connexion compte</p>
          </div>
        </div>
        
        <div class="mb-4">
          <p class="text-3xl font-bold text-primary-600">20€</p>
          <p class="text-sm text-gray-500">/mois</p>
        </div>
        
        <ul class="text-sm space-y-2 mb-6">
          <li class="flex items-start gap-2">
            <span class="text-green-500">✓</span>
            <span>Tous vos posts (même privés)</span>
          </li>
          <li class="flex items-start gap-2">
            <span class="text-green-500">✓</span>
            <span>Vos stories Instagram</span>
          </li>
          <li class="flex items-start gap-2">
            <span class="text-green-500">✓</span>
            <span>Limites API dédiées (200/h)</span>
          </li>
          <li class="flex items-start gap-2">
            <span class="text-green-500">✓</span>
            <span>Vos mentions et tags</span>
          </li>
        </ul>
        
        <button 
          @click="purchaseAddon('instagram_connection')"
          :class="hasAddon('instagram_connection') ? 'btn-secondary' : 'btn-primary'"
          class="btn w-full"
        >
          {{ hasAddon('instagram_connection') ? '✓ Activé' : 'Ajouter' }}
        </button>
      </div>
      
      <!-- Add-on Facebook Connection -->
      <div class="card">
        <div class="flex items-center gap-3 mb-4">
          <span class="text-4xl">👍</span>
          <div>
            <h4 class="font-bold text-lg">Facebook Avancé</h4>
            <p class="text-sm text-gray-600">Mode connexion compte</p>
          </div>
        </div>
        
        <div class="mb-4">
          <p class="text-3xl font-bold text-primary-600">20€</p>
          <p class="text-sm text-gray-500">/mois</p>
        </div>
        
        <ul class="text-sm space-y-2 mb-6">
          <li class="flex items-start gap-2">
            <span class="text-green-500">✓</span>
            <span>Vos pages Facebook complètes</span>
          </li>
          <li class="flex items-start gap-2">
            <span class="text-green-500">✓</span>
            <span>Posts privés de vos pages</span>
          </li>
          <li class="flex items-start gap-2">
            <span class="text-green-500">✓</span>
            <span>Limites API dédiées</span>
          </li>
          <li class="flex items-start gap-2">
            <span class="text-green-500">✓</span>
            <span>Statistiques avancées</span>
          </li>
        </ul>
        
        <button 
          @click="purchaseAddon('facebook_connection')"
          :class="hasAddon('facebook_connection') ? 'btn-secondary' : 'btn-primary'"
          class="btn w-full"
        >
          {{ hasAddon('facebook_connection') ? '✓ Activé' : 'Ajouter' }}
        </button>
      </div>
      
      <!-- Add-on Twitter Connection -->
      <div class="card">
        <div class="flex items-center gap-3 mb-4">
          <span class="text-4xl">🐦</span>
          <div>
            <h4 class="font-bold text-lg">Twitter Avancé</h4>
            <p class="text-sm text-gray-600">Mode connexion compte</p>
          </div>
        </div>
        
        <div class="mb-4">
          <p class="text-3xl font-bold text-primary-600">20€</p>
          <p class="text-sm text-gray-500">/mois</p>
        </div>
        
        <ul class="text-sm space-y-2 mb-6">
          <li class="flex items-start gap-2">
            <span class="text-green-500">✓</span>
            <span>Vos tweets et retweets</span>
          </li>
          <li class="flex items-start gap-2">
            <span class="text-green-500">✓</span>
            <span>Vos mentions</span>
          </li>
          <li class="flex items-start gap-2">
            <span class="text-green-500">✓</span>
            <span>Limites API dédiées (450/15min)</span>
          </li>
          <li class="flex items-start gap-2">
            <span class="text-green-500">✓</span>
            <span>Analytics complets</span>
          </li>
        </ul>
        
        <button 
          @click="purchaseAddon('twitter_connection')"
          :class="hasAddon('twitter_connection') ? 'btn-secondary' : 'btn-primary'"
          class="btn w-full"
        >
          {{ hasAddon('twitter_connection') ? '✓ Activé' : 'Ajouter' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const addons = ref([]);

// Charger les addons actifs
const loadAddons = async () => {
  try {
    const response = await axios.get('/api/tenant/addons');
    addons.value = response.data.addons;
  } catch (error) {
    console.error('Failed to load addons', error);
  }
};

const hasAddon = (addonKey) => {
  return addons.value.some(a => a.addon_key === addonKey && a.active);
};

const purchaseAddon = async (addonKey) => {
  if (hasAddon(addonKey)) {
    return;
  }
  
  try {
    // Créer checkout session Stripe pour l'addon
    const response = await axios.post('/stripe/addon/checkout', {
      addon: addonKey
    });
    
    if (response.data.url) {
      window.location.href = response.data.url;
    }
  } catch (error) {
    alert('Erreur lors de l\'achat de l\'add-on');
  }
};

onMounted(() => {
  loadAddons();
});
</script>
```

---

## 📅 **PHASE 6 : STRIPE ADD-ONS (2h)**

### **Tâche 6.1 : Modifier StripeController (1h30)**

**Fichier :** `app/Http/Controllers/StripeController.php`

**Ajouter ces méthodes :**

```php
/**
 * Créer checkout session pour add-on
 */
public function createAddonCheckout(Request $request)
{
    $tenant = $request->user()->tenant;
    $addonKey = $request->get('addon');
    
    $addons = config('plans.addons');
    $addon = $addons[$addonKey] ?? null;
    
    if (!$addon) {
        return response()->json(['error' => 'Add-on invalide'], 400);
    }
    
    try {
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price' => $addon['stripe_price'],
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'success_url' => route('billing') . '?addon_success=true&addon=' . $addonKey,
            'cancel_url' => route('billing') . '?canceled=true',
            'client_reference_id' => $tenant->id,
            'metadata' => [
                'tenant_id' => $tenant->id,
                'addon_key' => $addonKey,
            ],
        ]);

        return response()->json([
            'sessionId' => $session->id,
            'url' => $session->url,
        ]);
        
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

/**
 * Gérer succès add-on (webhook)
 */
protected function handleAddonSubscription($subscription)
{
    $metadata = $subscription->metadata;
    $tenantId = $metadata->tenant_id ?? null;
    $addonKey = $metadata->addon_key ?? null;
    
    if ($tenantId && $addonKey) {
        $tenant = \App\Models\Tenant::find($tenantId);
        
        if ($tenant) {
            $tenant->activateAddon($addonKey, [
                'stripe_subscription_id' => $subscription->id,
            ]);
        }
    }
}
```

---

### **Tâche 6.2 : Ajouter Route Stripe (15 min)**

**Fichier :** `routes/web.php`

**Ajouter dans le groupe auth :**

```php
// Stripe add-on checkout
Route::post('/stripe/addon/checkout', [StripeController::class, 'createAddonCheckout'])
    ->name('stripe.addon.checkout');
```

---

### **Tâche 6.3 : API Tenant Add-ons (15 min)**

**Fichier :** `routes/api.php`

**Ajouter :**

```php
// Tenant add-ons
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/tenant/addons', function() {
        $addons = \App\Models\TenantAddon::where('active', true)->get();
        return response()->json(['addons' => $addons]);
    });
});
```

---

## 📅 **PHASE 7 : TESTS & VALIDATION (4h)**

### **Tâche 7.1 : Tests Backend (2h)**

**Nouveau fichier :** `tests/Feature/HybridConnectionTest.php`

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\Feed;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HybridConnectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function feed_can_use_simple_mode_by_default()
    {
        $tenant = Tenant::factory()->create();
        $tenant->switchDatabase();
        
        $feed = Feed::factory()->create([
            'type' => 'instagram',
            'credentials' => null,
        ]);
        
        $service = app(\App\Services\FeedService::class);
        $this->assertEquals('simple', $service->getConnectionType($feed));
    }
    
    /** @test */
    public function feed_can_use_advanced_mode_with_credentials()
    {
        $tenant = Tenant::factory()->create();
        $tenant->switchDatabase();
        
        $feed = Feed::factory()->create([
            'type' => 'instagram',
            'credentials' => [
                'access_token' => 'custom_token',
                'user_id' => '123456',
            ],
        ]);
        
        $service = app(\App\Services\FeedService::class);
        $this->assertEquals('advanced', $service->getConnectionType($feed));
        $this->assertTrue($service->hasCustomCredentials($feed));
    }
    
    /** @test */
    public function tenant_can_activate_addon()
    {
        $tenant = Tenant::factory()->create(['plan' => 'business']);
        
        $result = $tenant->activateAddon('instagram_connection');
        
        $this->assertTrue($result);
        $this->assertTrue($tenant->hasAddon('instagram_connection'));
    }
    
    /** @test */
    public function enterprise_plan_can_use_advanced_mode_without_addon()
    {
        $tenant = Tenant::factory()->create(['plan' => 'enterprise']);
        
        $this->assertTrue($tenant->canUseAdvancedMode('instagram'));
        $this->assertTrue($tenant->canUseAdvancedMode('facebook'));
    }
    
    /** @test */
    public function starter_plan_cannot_use_advanced_mode_without_addon()
    {
        $tenant = Tenant::factory()->create(['plan' => 'starter']);
        
        $this->assertFalse($tenant->canUseAdvancedMode('instagram'));
    }
}
```

---

### **Tâche 7.2 : Tests E2E (2h)**

**Nouveau fichier :** `tests/Feature/OAuthFeedConnectionTest.php`

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OAuthFeedConnectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_initiate_instagram_connection()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->get('/connect/instagram?feed_id=1');
        
        // Devrait rediriger vers Instagram
        $this->assertEquals(302, $response->status());
    }
    
    /** @test */
    public function oauth_callback_stores_credentials_in_feed()
    {
        // Mock Socialite
        $socialUser = \Mockery::mock('Laravel\Socialite\Contracts\User');
        $socialUser->shouldReceive('token')->andReturn('instagram_token_123');
        $socialUser->shouldReceive('refreshToken')->andReturn('refresh_token_123');
        $socialUser->shouldReceive('id')->andReturn('instagram_user_123');
        $socialUser->shouldReceive('nickname')->andReturn('testuser');
        
        \Laravel\Socialite\Facades\Socialite::shouldReceive('driver->user')
            ->andReturn($socialUser);
        
        session(['connecting_feed_id' => 1]);
        
        $response = $this->get('/connect/instagram/callback');
        
        // Vérifier redirection
        $response->assertRedirect('/feeds');
        
        // Vérifier que les credentials sont stockés
        $feed = Feed::find(1);
        $this->assertNotNull($feed->credentials);
        $this->assertEquals('instagram_token_123', $feed->credentials['access_token']);
    }
    
    /** @test */
    public function user_can_disconnect_account()
    {
        $user = User::factory()->create();
        $feed = Feed::factory()->create([
            'credentials' => ['access_token' => 'test'],
        ]);
        
        $response = $this->actingAs($user)
            ->post("/feeds/{$feed->id}/disconnect");
        
        $feed->refresh();
        $this->assertNull($feed->credentials);
    }
}
```

---

## 📅 **PHASE 8 : DOCUMENTATION (2h)**

### **Tâche 8.1 : Guide Utilisateur (1h)**

**Nouveau fichier :** `GUIDE_MODE_AVANCE.md`

**Contenu :**

```markdown
# Guide Utilisateur - Mode Avancé

## Qu'est-ce que le Mode Avancé ?

Le mode avancé vous permet de connecter directement votre compte Instagram/Facebook/Twitter pour :
- Afficher TOUS vos posts (même privés)
- Afficher vos stories Instagram
- Afficher vos mentions
- Avoir des limites API dédiées (200/h)

## Prix

+20€/mois par connexion de compte

## Comment activer ?

1. Aller dans **Facturation**
2. Section **Add-ons Premium**
3. Cliquer sur **"Ajouter"** pour Instagram/Facebook/Twitter
4. Payer 20€/mois
5. Aller dans **Flux**
6. Créer un flux ou modifier un existant
7. Choisir **"Mode Avancé"**
8. Cliquer sur **"Connecter mon compte Instagram"**
9. Autoriser l'accès
10. ✅ Votre compte est connecté !

## Différence avec Mode Simple

| Fonctionnalité | Mode Simple | Mode Avancé |
|----------------|-------------|-------------|
| **Prix** | Inclus | +20€/mois |
| **Configuration** | 2 minutes | 5 minutes |
| **Posts publics** | ✅ | ✅ |
| **Posts privés** | ❌ | ✅ |
| **Stories** | ❌ | ✅ |
| **Mentions** | ❌ | ✅ |
| **Limites API** | Partagées | Dédiées |
```

---

### **Tâche 8.2 : Guide Admin (1h)**

**Nouveau fichier :** `ADMIN_HYBRID_GUIDE.md`

**Contenu :**

```markdown
# Guide Admin - Gestion Mode Hybride

## Configuration Stripe

1. Créer les prix add-ons dans Stripe Dashboard
2. Ajouter dans .env :
```env
STRIPE_PRICE_INSTAGRAM_CONNECTION=price_...
STRIPE_PRICE_FACEBOOK_CONNECTION=price_...
STRIPE_PRICE_TWITTER_CONNECTION=price_...
```

## Monitoring

Surveiller :
- Nombre de clients en mode simple vs avancé
- Revenus add-ons
- Utilisation API (limites)

## Support

Issues communes :
- Token expiré : Command `php artisan tokens:refresh`
- Client bloqué OAuth : Guide step-by-step
- Limite atteinte : Proposer mode avancé
```

---

## 📊 **DÉPENDANCES & ORDRE**

### **Ordre d'Exécution Obligatoire**

```
1. Phase 1 (Backend Core)
   └─> REQUIS pour Phase 2, 3, 4

2. Phase 2 (OAuth)
   └─> REQUIS pour Phase 3

3. Phase 5 (Pricing)
   └─> REQUIS pour Phase 6

4. Phase 6 (Stripe)
   └─> REQUIS pour Phase 3 (UI acheter add-on)

5. Phase 3 (Frontend)
   └─> Dépend de 1, 2, 5, 6

6. Phase 4 (Token Refresh)
   └─> Indépendant, peut être fait en parallèle

7. Phase 7 (Tests)
   └─> Après 1, 2, 3, 4, 5, 6

8. Phase 8 (Documentation)
   └─> En parallèle ou à la fin
```

---

## 📋 **CHECKLIST COMPLÈTE**

### **Phase 1 : Backend Core**
- [ ] Modifier `InstagramFeed.php` (1h30)
- [ ] Modifier `FacebookFeed.php` (1h30)
- [ ] Modifier `TwitterFeed.php` (1h30)
- [ ] Modifier `FeedService.php` (2h)
- [ ] Tests unitaires backend (1h30)

### **Phase 2 : OAuth Connection**
- [ ] Créer `FeedConnectionController.php` (3h)
- [ ] Ajouter routes web (30 min)
- [ ] Ajouter route API (15 min)

### **Phase 3 : Frontend UI**
- [ ] Créer `FeedConnectionModal.vue` (2h)
- [ ] Modifier `Feeds.vue` (2h)

### **Phase 4 : Token Refresh**
- [ ] Créer `TokenRefreshService.php` (1h)
- [ ] Créer commande `RefreshSocialTokens` (30 min)
- [ ] Ajouter au scheduler (15 min)

### **Phase 5 : Pricing & Plans**
- [ ] Modifier `config/plans.php` (30 min)
- [ ] Créer migration `tenant_addons` (30 min)
- [ ] Créer modèle `TenantAddon` (30 min)
- [ ] Ajouter méthodes dans `Tenant` (30 min)
- [ ] Modifier page `Billing.vue` (1h)

### **Phase 6 : Stripe Add-ons**
- [ ] Modifier `StripeController` (1h30)
- [ ] Ajouter route Stripe (15 min)
- [ ] API tenant addons (15 min)

### **Phase 7 : Tests**
- [ ] Tests backend (2h)
- [ ] Tests E2E (2h)

### **Phase 8 : Documentation**
- [ ] Guide utilisateur (1h)
- [ ] Guide admin (1h)

---

## ⏱️ **TIMELINE GLOBALE**

### **Sprint 1 (Semaine 1) - Backend**
```
Jour 1 : Phase 1 (8h) - Backend Core
Jour 2 : Phase 2 (6h) - OAuth Connection
Jour 3 : Phase 4 (2h) - Token Refresh
         + Phase 5 (3h) - Pricing
```

### **Sprint 2 (Semaine 2) - Frontend & Finition**
```
Jour 4 : Phase 3 (4h) - Frontend UI
         + Phase 6 (2h) - Stripe
Jour 5 : Phase 7 (4h) - Tests & QA
         + Phase 8 (2h) - Documentation
```

**TOTAL : 2 semaines (26h de dev)**

---

## 💰 **COÛT DÉVELOPPEMENT**

### **Estimation**

```
Développeur Laravel/Vue : 80€/h
26 heures × 80€ = 2,080€

OU

Développeur Junior : 50€/h
35 heures × 50€ = 1,750€
```

### **ROI**

```
Investissement : 2,080€

Revenus add-ons :
- 10 clients × 20€/mois = 200€/mois
- 50 clients × 20€/mois = 1,000€/mois
- 100 clients × 20€/mois = 2,000€/mois

Break-even :
- Avec 10 clients : 11 mois
- Avec 50 clients : 3 mois ✅
- Avec 100 clients : 2 mois ✅
```

---

## 📁 **FICHIERS À CRÉER (11 nouveaux)**

1. `app/Http/Controllers/FeedConnectionController.php`
2. `app/Services/TokenRefreshService.php`
3. `app/Console/Commands/RefreshSocialTokens.php`
4. `app/Models/TenantAddon.php`
5. `database/migrations/tenant/create_tenant_addons_table.php`
6. `resources/js/Components/FeedConnectionModal.vue`
7. `tests/Unit/HybridFeedTest.php`
8. `tests/Feature/HybridConnectionTest.php`
9. `tests/Feature/OAuthFeedConnectionTest.php`
10. `GUIDE_MODE_AVANCE.md`
11. `ADMIN_HYBRID_GUIDE.md`

---

## 📝 **FICHIERS À MODIFIER (10 existants)**

1. `app/Services/Feeds/InstagramFeed.php` - Constructor hybride
2. `app/Services/Feeds/FacebookFeed.php` - Constructor hybride
3. `app/Services/Feeds/TwitterFeed.php` - Constructor hybride
4. `app/Services/FeedService.php` - Passer credentials
5. `app/Models/Tenant.php` - Méthodes addons
6. `config/plans.php` - Add-ons pricing
7. `routes/web.php` - Routes OAuth feeds
8. `routes/api.php` - API addons
9. `app/Http/Controllers/StripeController.php` - Checkout addons
10. `resources/js/Pages/Dashboard/Feeds.vue` - UI hybride
11. `resources/js/Pages/Dashboard/Billing.vue` - Section addons
12. `routes/console.php` - Scheduler refresh

**Total : 23 fichiers**

---

## 🎯 **PRIORITÉS**

### **Critique (Must Have)**
```
✅ Phase 1 : Backend Core
✅ Phase 2 : OAuth Connection
✅ Phase 3 : Frontend UI
→ Sans ça, la feature ne fonctionne pas
```

### **Important (Should Have)**
```
✅ Phase 4 : Token Refresh
✅ Phase 5 : Pricing
→ Pour la monétisation et maintenance
```

### **Nice to Have**
```
✅ Phase 6 : Stripe Integration
✅ Phase 7 : Tests complets
✅ Phase 8 : Documentation
→ Améliore l'expérience mais pas bloquant
```

---

## 🚀 **STRATÉGIE DE DÉPLOIEMENT**

### **Version 1 : MVP Hybride (Sprint 1)**

```
Features :
✅ Mode simple (existant)
✅ Mode avancé (connexion manuelle)
❌ Pas de Stripe add-on
❌ Pas de refresh auto

Rollout :
→ Beta avec 5-10 clients
→ Collecter feedback
→ Ajuster

Temps : 1 semaine
```

### **Version 2 : Complet (Sprint 2)**

```
Features :
✅ Mode simple
✅ Mode avancé OAuth
✅ Stripe add-ons
✅ Refresh automatique
✅ UI complète

Rollout :
→ Tous les clients
→ Email announcement
→ Support ready

Temps : 2 semaines
```

---

## 📊 **MÉTRIQUES DE SUCCÈS**

### **Techniques**

- [ ] Tous les tests passent (100%)
- [ ] 0 breaking change (rétrocompatible)
- [ ] Performance : Temps sync < 500ms
- [ ] Taux erreur OAuth < 5%

### **Business**

- [ ] 10% des clients passent en mode avancé (mois 1)
- [ ] 20% des clients passent en mode avancé (mois 3)
- [ ] +15% de revenus avec add-ons (mois 6)
- [ ] Taux churn inchangé ou amélioré

### **Support**

- [ ] Tickets support < +20% (acceptable)
- [ ] Satisfaction client > 4/5
- [ ] Temps résolution < 24h

---

## ✅ **RÉSUMÉ DU PLAN**

### **Développement**
- **Durée** : 2 semaines (26h)
- **Fichiers** : 23 fichiers (11 nouveaux, 12 modifiés)
- **Coût** : 2,080€ (dev senior) ou 1,750€ (junior)

### **Phases**
1. ✅ Backend Core (8h)
2. ✅ OAuth Connection (6h)
3. ✅ Frontend UI (4h)
4. ✅ Token Refresh (2h)
5. ✅ Pricing (3h)
6. ✅ Stripe (2h)
7. ✅ Tests (4h)
8. ✅ Documentation (2h)

### **ROI**
- Break-even : 2-3 mois avec 50+ clients
- Revenue add-on : +20€/mois par client
- Potentiel : +2,000€/mois avec 100 clients

### **Risques**
- 🟡 Complexité OAuth (mitigation : tests)
- 🟡 Support overhead (mitigation : docs)
- 🟢 Technique faisable (architecture prête)

---

## 🎊 **PLAN COMPLET TERMINÉ**

**Documentation créée :** `PLAN_IMPLEMENTATION_HYBRIDE.md`

**Contient :**
- ✅ 8 phases détaillées
- ✅ Code exact à ajouter/modifier
- ✅ 23 fichiers listés
- ✅ Timeline 2 semaines
- ✅ Tests inclus
- ✅ ROI calculé
- ✅ Checklist complète

**Aucune modification effectuée** ✅

**Prêt pour implémentation quand tu décides !** 🚀

