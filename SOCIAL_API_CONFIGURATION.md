# 🔑 Configuration des API Sociales - Guide Complet

## 📋 Table des Matières

1. [Instagram](#-1-instagram)
2. [Facebook](#-2-facebook)
3. [Twitter/X](#-3-twitterx)
4. [Google Reviews](#-4-google-reviews)
5. [Authentification Sociale (Bonus)](#-5-authentification-sociale-bonus)
6. [Troubleshooting](#-troubleshooting)

---

## 📸 **1. INSTAGRAM**

### **Étape 1 : Créer une Application Facebook (15 min)**

Instagram API est maintenant géré via Facebook Developer Console.

#### 1.1 Créer un compte développeur Facebook

1. Aller sur [Facebook Developers](https://developers.facebook.com)
2. Se connecter avec votre compte Facebook
3. Accepter les conditions développeur

#### 1.2 Créer une nouvelle application

1. Cliquer sur **"Mes Apps"** → **"Créer une app"**
2. Sélectionner **"Consommateur"** comme type d'app
3. Remplir les informations :
   - **Nom de l'app** : HashMyTag Social Wall
   - **Email de contact** : votre@email.com
   - **Objet de l'app** : Marketing
4. Cliquer sur **"Créer une app"**

#### 1.3 Activer Instagram Basic Display

1. Dans le tableau de bord, chercher **"Instagram Basic Display"**
2. Cliquer sur **"Configurer"**
3. Descendre et cliquer sur **"Créer une nouvelle app"**
4. Remplir :
   - **Display Name** : HashMyTag
   - **Valid OAuth Redirect URIs** : 
     ```
     https://yourdomain.com/auth/instagram/callback
     http://localhost:8000/auth/instagram/callback
     ```
   - **Deauthorize Callback URL** : `https://yourdomain.com/auth/instagram/deauthorize`
   - **Data Deletion Request URL** : `https://yourdomain.com/auth/instagram/delete`

5. Cliquer sur **"Enregistrer les modifications"**

#### 1.4 Obtenir les credentials

1. Dans la section **"Instagram Basic Display"**, noter :
   - **Instagram App ID**
   - **Instagram App Secret**

2. Descendre à **"User Token Generator"**
3. Cliquer sur **"Ajouter ou supprimer des testeurs Instagram"**
4. Ajouter votre compte Instagram comme testeur
5. Accepter l'invitation sur Instagram
6. Revenir et cliquer sur **"Generate Token"**
7. **Copier le token généré** (valide 60 jours)

### **Étape 2 : Configuration dans Laravel**

Éditer le fichier `.env` :

```env
# Instagram API
INSTAGRAM_APP_ID=votre_app_id
INSTAGRAM_APP_SECRET=votre_app_secret
INSTAGRAM_ACCESS_TOKEN=votre_access_token
INSTAGRAM_USER_ID=votre_user_id
```

### **Étape 3 : Tester la connexion**

```bash
php artisan tinker
```

```php
$instagram = app(\App\Services\Feeds\InstagramFeed::class);
$posts = $instagram->fetch(['hashtags' => ['test']]);
dd($posts);
```

### **📝 Notes importantes Instagram**

- ⚠️ **Token expiration** : Les tokens expirent après 60 jours
- 🔄 **Renouvellement** : Utiliser le refresh token pour prolonger
- 📊 **Limites** : 200 requêtes/heure par utilisateur
- 🔒 **Permissions** : `instagram_graph_user_profile`, `instagram_graph_user_media`

---

## 👍 **2. FACEBOOK**

### **Étape 1 : Utiliser la même app Facebook**

Si vous avez déjà créé l'app pour Instagram, réutilisez-la !

#### 1.1 Activer Facebook Graph API

1. Dans le Dashboard Facebook Developers
2. Chercher **"Facebook Login"** → **"Configurer"**
3. Sélectionner **"Web"**
4. Entrer votre URL : `https://yourdomain.com`

#### 1.2 Configurer OAuth Redirect

1. Dans **"Facebook Login"** → **"Paramètres"**
2. Ajouter les **URI de redirection OAuth valides** :
   ```
   https://yourdomain.com/auth/facebook/callback
   http://localhost:8000/auth/facebook/callback
   ```

#### 1.3 Obtenir les permissions

1. Aller dans **"Outils"** → **"Explorateur d'API Graph"**
2. Sélectionner votre application
3. Cliquer sur **"Obtenir un jeton"** → **"Obtenir un jeton d'accès utilisateur"**
4. Sélectionner les permissions :
   - ✅ `pages_show_list`
   - ✅ `pages_read_engagement`
   - ✅ `pages_read_user_content`
5. Cliquer sur **"Générer un jeton d'accès"**
6. **Copier le token**

#### 1.4 Obtenir le Page ID

1. Aller sur votre page Facebook
2. Dans **"À propos"** → noter l'ID de la page
3. Ou utiliser : `https://findmyfbid.com/`

### **Étape 2 : Configuration dans Laravel**

Éditer le fichier `.env` :

```env
# Facebook API
FACEBOOK_APP_ID=votre_app_id
FACEBOOK_APP_SECRET=votre_app_secret
FACEBOOK_ACCESS_TOKEN=votre_access_token
```

### **Étape 3 : Créer un flux Facebook**

Dans le dashboard HashMyTag :
1. Aller dans **"Flux"**
2. Créer un nouveau flux
3. Type : **Facebook**
4. Configuration :
   ```json
   {
     "page_id": "123456789",
     "hashtags": ["votrehashtag"]
   }
   ```

### **📝 Notes importantes Facebook**

- ⏰ **Token expiration** : 60 jours pour user token
- 🔄 **Page token** : Peut être permanent si configuré
- 📊 **Limites** : 200 appels/heure par utilisateur
- 🔒 **Révision d'app** : Nécessaire pour production (permissions avancées)

---

## 🐦 **3. TWITTER/X**

### **Étape 1 : Créer un compte développeur Twitter (10 min)**

#### 1.1 S'inscrire comme développeur

1. Aller sur [Twitter Developer Portal](https://developer.twitter.com/en/portal/dashboard)
2. Se connecter avec votre compte Twitter/X
3. Cliquer sur **"Sign up for Free Account"**
4. Remplir le formulaire :
   - **Use case** : Building tools for Twitter users
   - **Description** : Social media aggregation tool
5. Accepter les conditions

#### 1.2 Créer une application

1. Dans le Dashboard, cliquer sur **"Create Project"**
2. Remplir :
   - **Project name** : HashMyTag
   - **Use case** : Making a bot
   - **Description** : Social wall aggregator
3. Cliquer sur **"Next"**

#### 1.3 Créer une app

1. **App name** : hashmytag-production
2. Cliquer sur **"Complete"**
3. **Sauvegarder les clés** affichées :
   - API Key
   - API Key Secret
   - Bearer Token

### **Étape 2 : Obtenir Elevated Access (Important !)**

#### 2.1 Demander Elevated Access

1. Dans le Dashboard, cliquer sur votre projet
2. Cliquer sur **"Apply for Elevated"**
3. Remplir le formulaire détaillé :
   - Expliquer votre cas d'usage
   - Expliquer comment vous utilisez l'API
   - Mentions des hashtags, pas de données sensibles
4. Soumettre et **attendre l'approbation** (quelques heures à 3 jours)

⚠️ **Sans Elevated Access, vous ne pourrez faire que 500,000 tweets/mois**

### **Étape 3 : Configuration dans Laravel**

Éditer le fichier `.env` :

```env
# Twitter API v2
TWITTER_API_KEY=votre_api_key
TWITTER_API_SECRET=votre_api_secret
TWITTER_BEARER_TOKEN=votre_bearer_token
```

### **Étape 4 : Tester la connexion**

```bash
php artisan tinker
```

```php
$twitter = app(\App\Services\Feeds\TwitterFeed::class);
$posts = $twitter->fetch(['hashtags' => ['tech']]);
dd($posts);
```

### **📝 Notes importantes Twitter**

- 🆓 **Free tier** : 500,000 tweets/mois
- ⬆️ **Elevated** : Nécessaire pour usage sérieux
- 💰 **Pro tier** : $100/mois pour 1M tweets
- 📊 **Rate limits** : 450 requêtes/15min
- 🔄 **API v2** : Nouvelle version, plus performante

---

## ⭐ **4. GOOGLE REVIEWS**

### **Étape 1 : Créer un projet Google Cloud (10 min)**

#### 1.1 Accéder à Google Cloud Console

1. Aller sur [Google Cloud Console](https://console.cloud.google.com)
2. Se connecter avec votre compte Google
3. Accepter les conditions si première fois

#### 1.2 Créer un nouveau projet

1. Cliquer sur la liste déroulante en haut
2. Cliquer sur **"Nouveau projet"**
3. Remplir :
   - **Nom du projet** : HashMyTag
   - **Organisation** : Aucune (ou votre organisation)
4. Cliquer sur **"Créer"**

### **Étape 2 : Activer les API nécessaires**

#### 2.1 Activer Places API

1. Dans le menu, aller dans **"API et services"** → **"Bibliothèque"**
2. Chercher **"Places API"**
3. Cliquer sur **"Places API"**
4. Cliquer sur **"Activer"**

#### 2.2 Activer My Business API (optionnel)

1. Chercher **"My Business Business Information API"**
2. Cliquer sur **"Activer"**

### **Étape 3 : Créer des credentials**

#### 3.1 Créer une clé API

1. Aller dans **"API et services"** → **"Identifiants"**
2. Cliquer sur **"Créer des identifiants"** → **"Clé API"**
3. **Copier la clé générée**
4. Cliquer sur **"Restreindre la clé"** (recommandé)

#### 3.2 Restreindre la clé API (sécurité)

1. Dans **"Restrictions relatives aux applications"** :
   - Sélectionner **"Adresses IP"**
   - Ajouter l'IP de votre serveur
   
   OU
   
   - Sélectionner **"Référents HTTP"**
   - Ajouter : `yourdomain.com/*`

2. Dans **"Restrictions relatives aux API"** :
   - Sélectionner **"Restreindre la clé"**
   - Cocher ✅ **Places API**

3. Cliquer sur **"Enregistrer"**

#### 3.3 Créer OAuth 2.0 (pour My Business)

1. Cliquer sur **"Créer des identifiants"** → **"ID client OAuth"**
2. Type d'application : **Application Web**
3. Remplir :
   - **Nom** : HashMyTag OAuth
   - **URI de redirection autorisés** :
     ```
     https://yourdomain.com/auth/google/callback
     http://localhost:8000/auth/google/callback
     ```
4. Cliquer sur **"Créer"**
5. **Copier** :
   - Client ID
   - Client Secret

### **Étape 4 : Obtenir le Place ID**

#### Option A : Via Google Maps

1. Aller sur [Google Maps](https://www.google.com/maps)
2. Chercher votre établissement
3. Cliquer sur "Partager"
4. Dans l'URL, copier le code après `!1s` et avant `!`
5. Ou utiliser : `https://developers.google.com/maps/documentation/javascript/examples/places-placeid-finder`

#### Option B : Via l'API

```bash
curl "https://maps.googleapis.com/maps/api/place/findplacefromtext/json?input=MonEntreprise&inputtype=textquery&fields=place_id&key=VOTRE_API_KEY"
```

### **Étape 5 : Configuration dans Laravel**

Éditer le fichier `.env` :

```env
# Google APIs
GOOGLE_CLIENT_ID=votre_client_id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=votre_client_secret
GOOGLE_API_KEY=votre_api_key
```

### **Étape 6 : Créer un flux Google Reviews**

Dans le dashboard HashMyTag :
1. Aller dans **"Flux"**
2. Créer un nouveau flux
3. Type : **Google Reviews**
4. Configuration :
   ```json
   {
     "place_id": "ChIJ..."
   }
   ```

### **📝 Notes importantes Google**

- 💰 **Coût** : $0.017 par requête Places API (200$ de crédit gratuit/mois)
- 🔒 **Restrictions** : Restreindre la clé API par domaine/IP
- 📊 **Quotas** : 10,000 requêtes/jour par défaut
- ⭐ **Reviews** : Uniquement les avis 4+ étoiles affichés

---

## 🔐 **5. AUTHENTIFICATION SOCIALE (BONUS)**

Si vous voulez permettre aux utilisateurs de se connecter avec leurs comptes sociaux.

### **Étape 1 : Installer Laravel Socialite**

```bash
composer require laravel/socialite
```

### **Étape 2 : Configuration**

Ajouter dans `config/services.php` :

```php
return [
    // ... autres services

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('APP_URL') . '/auth/facebook/callback',
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('APP_URL') . '/auth/google/callback',
    ],

    'twitter' => [
        'client_id' => env('TWITTER_CLIENT_ID'),
        'client_secret' => env('TWITTER_CLIENT_SECRET'),
        'redirect' => env('APP_URL') . '/auth/twitter/callback',
    ],
];
```

### **Étape 3 : Créer le contrôleur**

```bash
php artisan make:controller Auth/SocialAuthController
```

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    /**
     * Rediriger vers le provider
     */
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Gérer le callback
     */
    public function callback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['error' => 'Erreur d\'authentification']);
        }

        // Chercher ou créer l'utilisateur
        $user = User::where('email', $socialUser->getEmail())->first();

        if (!$user) {
            $user = User::create([
                'name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                'password' => bcrypt(Str::random(24)),
                'email_verified_at' => now(),
            ]);
        }

        Auth::login($user);

        return redirect('/dashboard');
    }
}
```

### **Étape 4 : Routes**

Ajouter dans `routes/web.php` :

```php
use App\Http\Controllers\Auth\SocialAuthController;

Route::get('auth/{provider}', [SocialAuthController::class, 'redirect'])
    ->name('social.redirect');
    
Route::get('auth/{provider}/callback', [SocialAuthController::class, 'callback'])
    ->name('social.callback');
```

### **Étape 5 : Ajouter les boutons dans la vue**

```vue
<!-- resources/js/Pages/Auth/Login.vue -->
<template>
  <div class="social-login">
    <a :href="`/auth/facebook`" class="btn btn-facebook">
      Se connecter avec Facebook
    </a>
    <a :href="`/auth/google`" class="btn btn-google">
      Se connecter avec Google
    </a>
    <a :href="`/auth/twitter`" class="btn btn-twitter">
      Se connecter avec Twitter
    </a>
  </div>
</template>
```

---

## 🆘 **TROUBLESHOOTING**

### **Problème : "Invalid OAuth redirect URI"**

**Solution :**
- Vérifier que l'URL dans les paramètres de l'app correspond EXACTEMENT
- Inclure `http://` ou `https://`
- Pas de slash final `/`

### **Problème : "Token expired"**

**Solutions :**
- **Instagram** : Utiliser le refresh token
- **Facebook** : Générer un Page Access Token (permanent)
- **Twitter** : Bearer token ne expire pas
- **Google** : Utiliser OAuth refresh token

### **Problème : "Rate limit exceeded"**

**Solutions :**
- Augmenter l'intervalle de synchronisation
- Mettre en cache les résultats
- Utiliser des queues pour espacer les requêtes
- Upgrade vers plan payant si nécessaire

### **Problème : "Permission denied"**

**Solutions :**
- Vérifier que toutes les permissions sont accordées
- Soumettre l'app pour révision (Facebook)
- Demander Elevated Access (Twitter)
- Activer les APIs dans Google Cloud Console

### **Problème : "No data returned"**

**Solutions :**
- Vérifier que le hashtag existe et a des posts
- Vérifier que le compte est public
- Vérifier les logs : `storage/logs/laravel.log`
- Tester l'API directement avec Postman

---

## 📊 **TABLEAU RÉCAPITULATIF**

| Plateforme | Temps Setup | Coût | Limites | Difficulté |
|------------|-------------|------|---------|------------|
| **Instagram** | 15 min | Gratuit | 200/h | ⭐⭐ Moyen |
| **Facebook** | 10 min | Gratuit | 200/h | ⭐⭐ Moyen |
| **Twitter** | 15 min | Gratuit/100$ | 450/15min | ⭐⭐⭐ Difficile |
| **Google Reviews** | 10 min | 200$/mois gratuit | 10k/jour | ⭐ Facile |

---

## ✅ **CHECKLIST CONFIGURATION**

### Instagram
- [ ] Compte Facebook Developer créé
- [ ] Application créée
- [ ] Instagram Basic Display activé
- [ ] Token généré
- [ ] Credentials dans .env
- [ ] Testé avec artisan tinker

### Facebook
- [ ] Même app que Instagram
- [ ] Facebook Login configuré
- [ ] Permissions obtenues
- [ ] Page ID récupéré
- [ ] Token dans .env
- [ ] Premier flux créé

### Twitter
- [ ] Compte Developer créé
- [ ] Application créée
- [ ] Elevated Access demandé (et approuvé)
- [ ] Bearer token récupéré
- [ ] Credentials dans .env
- [ ] Testé avec un hashtag

### Google
- [ ] Projet Google Cloud créé
- [ ] Places API activée
- [ ] Clé API créée et restreinte
- [ ] Place ID récupéré
- [ ] Credentials dans .env
- [ ] Premier flux créé

---

## 🎯 **ORDRE RECOMMANDÉ**

### Pour débuter (MVP) :

1. **Google Reviews** ⭐ (Le plus simple)
   - Setup : 10 minutes
   - Pas de révision nécessaire
   - Données stables

2. **Twitter** 🐦 (Assez simple)
   - Setup : 15 minutes
   - Elevated Access peut prendre 1-3 jours
   - Beaucoup de contenus

3. **Facebook** 👍 (Moyen)
   - Setup : 10 minutes
   - Réutilise l'app Instagram
   - Bon pour les pages entreprises

4. **Instagram** 📸 (Plus complexe)
   - Setup : 15 minutes
   - Token expiration à gérer
   - Limites plus strictes

---

## 📞 **SUPPORT**

### Liens utiles :

- **Instagram** : https://developers.facebook.com/docs/instagram-basic-display-api
- **Facebook** : https://developers.facebook.com/docs/graph-api
- **Twitter** : https://developer.twitter.com/en/docs/twitter-api
- **Google** : https://developers.google.com/maps/documentation/places/web-service

### En cas de problème :

1. Vérifier les logs : `storage/logs/laravel.log`
2. Tester l'API directement (Postman)
3. Vérifier que les tokens n'ont pas expiré
4. Consulter la documentation officielle
5. Ouvrir une issue sur GitHub

---

## 🎉 **PROCHAINES ÉTAPES**

Après configuration :

1. ✅ Créer votre premier flux
2. ✅ Synchroniser : `php artisan feeds:sync`
3. ✅ Voir les posts dans le dashboard
4. ✅ Tester le widget
5. ✅ Partager avec vos clients !

---

**⏱️ Temps total estimé : 1-2 heures pour tout configurer**

**💰 Coût total : 0€ (avec quotas gratuits)**

**🚀 Résultat : Votre mur social live est opérationnel !**

