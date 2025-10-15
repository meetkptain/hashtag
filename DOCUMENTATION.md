# 📚 HashMyTag - Documentation Complète

## Table des Matières

1. [Installation](#installation)
2. [Configuration](#configuration)
3. [Gestion des Flux](#gestion-des-flux)
4. [Widget JavaScript](#widget-javascript)
5. [Gamification](#gamification)
6. [API Documentation](#api-documentation)
7. [Personnalisation](#personnalisation)
8. [Commandes Artisan](#commandes-artisan)
9. [Déploiement](#déploiement)
10. [Troubleshooting](#troubleshooting)

---

## 📦 Installation

### Prérequis

- PHP 8.1 ou supérieur
- Composer 2.x
- Node.js 16.x ou supérieur
- MySQL 8.0 ou supérieur
- Extension PHP : `pdo_mysql`, `mbstring`, `xml`, `ctype`, `json`, `bcmath`

### Étapes d'Installation

```bash
# 1. Cloner le repository
git clone https://github.com/your-org/hashmytag.git
cd hashmytag

# 2. Installer les dépendances PHP
composer install

# 3. Installer les dépendances Node.js
npm install

# 4. Créer le fichier .env
cp .env.example .env

# 5. Générer la clé d'application
php artisan key:generate

# 6. Configurer la base de données dans .env
# Modifier DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 7. Exécuter les migrations
php artisan migrate

# 8. Créer le lien symbolique pour le storage
php artisan storage:link

# 9. Compiler les assets
npm run build

# 10. Démarrer le serveur de développement
php artisan serve
```

L'application sera accessible sur `http://localhost:8000`

---

## ⚙️ Configuration

### Configuration de Base

Éditez le fichier `.env` avec vos paramètres :

```env
APP_NAME="HashMyTag Social Wall"
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hashmytag
DB_USERNAME=root
DB_PASSWORD=
```

### Configuration Stripe

1. Créez un compte sur [Stripe](https://stripe.com)
2. Récupérez vos clés API depuis le Dashboard
3. Configurez dans `.env` :

```env
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

4. Créez les produits et prix dans Stripe
5. Mettez à jour `config/plans.php` avec les IDs des prix

### Configuration des API Sociales

#### Instagram

```env
INSTAGRAM_APP_ID=your_app_id
INSTAGRAM_APP_SECRET=your_app_secret
INSTAGRAM_ACCESS_TOKEN=your_access_token
```

**Obtenir les credentials :**
1. Allez sur [Facebook Developers](https://developers.facebook.com)
2. Créez une nouvelle app
3. Activez "Instagram Basic Display"
4. Suivez le processus d'authentification
5. Récupérez votre access token

#### Facebook

```env
FACEBOOK_APP_ID=your_app_id
FACEBOOK_APP_SECRET=your_app_secret
FACEBOOK_ACCESS_TOKEN=your_access_token
```

**Obtenir les credentials :**
1. Utilisez la même app Facebook
2. Activez "Facebook Graph API"
3. Générez un access token avec les permissions `pages_read_engagement`, `pages_show_list`

#### Twitter/X

```env
TWITTER_API_KEY=your_api_key
TWITTER_API_SECRET=your_api_secret
TWITTER_BEARER_TOKEN=your_bearer_token
```

**Obtenir les credentials :**
1. Créez une app sur [Twitter Developer Portal](https://developer.twitter.com)
2. Appliquez pour l'accès Elevated
3. Générez vos clés API v2

#### Google Reviews

```env
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
GOOGLE_API_KEY=your_api_key
```

**Obtenir les credentials :**
1. Créez un projet sur [Google Cloud Console](https://console.cloud.google.com)
2. Activez "Google My Business API"
3. Créez des credentials OAuth 2.0

---

## 📡 Gestion des Flux

### Créer un Flux

#### Via l'interface admin

1. Connectez-vous au dashboard
2. Allez dans "Flux"
3. Cliquez sur "Ajouter un flux"
4. Remplissez le formulaire :
   - **Nom** : Identifiant du flux
   - **Type** : Instagram, Facebook, Twitter, Google Reviews
   - **Configuration** : Hashtags, Page ID, Place ID selon le type

#### Via l'API

```bash
curl -X POST https://yourdomain.com/api/feeds \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Mon flux Instagram",
    "type": "instagram",
    "config": {
      "hashtags": ["marketing", "business", "tech"]
    }
  }'
```

### Types de Flux

#### Instagram

```json
{
  "name": "Instagram Marketing",
  "type": "instagram",
  "config": {
    "hashtags": ["marketing", "digital", "socialmedia"]
  }
}
```

#### Facebook

```json
{
  "name": "Page Facebook",
  "type": "facebook",
  "config": {
    "page_id": "123456789",
    "hashtags": ["optional"]
  }
}
```

#### Twitter/X

```json
{
  "name": "Twitter Tech",
  "type": "twitter",
  "config": {
    "hashtags": ["tech", "startup", "innovation"]
  }
}
```

#### Google Reviews

```json
{
  "name": "Avis Google",
  "type": "google_reviews",
  "config": {
    "place_id": "ChIJ..."
  }
}
```

### Synchronisation des Flux

#### Automatique

Les flux sont synchronisés automatiquement toutes les 5 minutes via le scheduler Laravel :

```php
// routes/console.php
Schedule::command('feeds:sync')->everyFiveMinutes();
```

#### Manuelle

```bash
# Synchroniser tous les flux
php artisan feeds:sync

# Synchroniser un tenant spécifique
php artisan feeds:sync --tenant=1
```

---

## 🎨 Widget JavaScript

### Installation de Base

Copiez ce code sur votre site web :

```html
<div id="hashmytag-wall"></div>
<script src="https://yourdomain.com/widget.js"></script>
<script>
  HashMyTag.init({
    apiKey: 'hmt_your_api_key_here',
    theme: 'light',
    direction: 'vertical',
    speed: 'medium',
    gamification: true,
    fullscreen: true
  });
</script>
```

### Options de Configuration

| Option | Type | Défaut | Description |
|--------|------|--------|-------------|
| `apiKey` | string | - | **Requis** - Votre clé API tenant |
| `theme` | string | 'light' | Thème du widget : 'light', 'dark', 'custom' |
| `direction` | string | 'vertical' | Direction du défilement : 'vertical', 'horizontal' |
| `speed` | string | 'medium' | Vitesse : 'slow', 'medium', 'fast' |
| `gamification` | boolean | true | Activer la gamification |
| `fullscreen` | boolean | true | Bouton plein écran |
| `autoplay` | boolean | true | Défilement automatique |
| `postsPerView` | number | 3 | Nombre de posts visibles |

### Exemples d'Utilisation

#### Widget Minimal

```html
<div id="hashmytag-wall"></div>
<script src="https://yourdomain.com/widget.js"></script>
<script>
  HashMyTag.init({
    apiKey: 'hmt_...'
  });
</script>
```

#### Widget Personnalisé

```html
<div id="hashmytag-wall"></div>
<script src="https://yourdomain.com/widget.js"></script>
<script>
  HashMyTag.init({
    apiKey: 'hmt_...',
    theme: 'dark',
    direction: 'horizontal',
    speed: 'fast',
    gamification: true,
    fullscreen: true,
    autoplay: true,
    postsPerView: 4
  });
</script>
```

#### Widget pour TV / Affichage

```html
<div id="hashmytag-wall"></div>
<script src="https://yourdomain.com/widget.js"></script>
<script>
  HashMyTag.init({
    apiKey: 'hmt_...',
    theme: 'dark',
    direction: 'vertical',
    speed: 'slow',
    gamification: true,
    fullscreen: false,
    autoplay: true,
    postsPerView: 6
  });
  
  // Auto-fullscreen au chargement
  setTimeout(() => {
    document.querySelector('.hashmytag-widget').classList.add('fullscreen');
  }, 1000);
</script>
```

### Responsive Design

Le widget s'adapte automatiquement :

- **Desktop** : Grille multi-colonnes
- **Tablette** : 2 colonnes
- **Mobile** : 1 colonne
- **TV** : Grille optimisée pour grand écran

---

## 🎮 Gamification

### Fonctionnalités Intégrées

#### 1. Badges "Nouveau"

Les posts récents (< 10 affichages) reçoivent un badge ✨ "Nouveau" animé.

```css
.gamification-badge {
  background: #10b981;
  animation: pulse 2s infinite;
}
```

#### 2. Surbrillance des Hashtags

Les posts contenant vos hashtags sont automatiquement surlignés :

```css
.hashmytag-post.is-highlighted {
  border-color: var(--highlight-color);
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
}
```

#### 3. Compteur d'Activité

Affichage en temps réel du nombre de posts et de l'index actuel.

#### 4. Animations d'Entrée

Chaque post apparaît avec une animation `fadeInUp` :

```css
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
```

### Configuration

Activez/désactivez la gamification :

```javascript
HashMyTag.init({
  apiKey: 'hmt_...',
  gamification: true  // ou false
});
```

Ou via l'interface admin : **Paramètres → Comportement → Gamification**

---

## 🔌 API Documentation

### Authentification

#### Widget API (API Key)

```bash
curl https://yourdomain.com/api/widget/posts?api_key=hmt_...
```

#### Admin API (Bearer Token)

```bash
curl https://yourdomain.com/api/feeds \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Endpoints

#### Widget Endpoints

**GET /api/widget/config**
```json
{
  "tenant": {
    "name": "Mon Entreprise",
    "branding": {...}
  },
  "settings": {
    "theme": "light",
    "direction": "vertical",
    "speed": "medium",
    ...
  }
}
```

**GET /api/widget/posts**
```json
{
  "posts": [
    {
      "id": 1,
      "platform": "instagram",
      "content": "...",
      "author": {...},
      "media": [...],
      "stats": {...},
      "is_new": true,
      "is_highlighted": false
    }
  ],
  "count": 50
}
```

**POST /api/widget/posts/{id}/view**

Track view event

**POST /api/widget/posts/{id}/interaction**
```json
{
  "type": "click",
  "duration": 5000
}
```

#### Admin Endpoints

**GET /api/feeds**

Liste des flux

**POST /api/feeds**
```json
{
  "name": "Mon flux",
  "type": "instagram",
  "config": {
    "hashtags": ["marketing"]
  }
}
```

**PUT /api/feeds/{id}**

Modifier un flux

**DELETE /api/feeds/{id}**

Supprimer un flux

**POST /api/feeds/{id}/sync**

Synchroniser manuellement un flux

**GET /api/analytics**

Statistiques globales

**GET /api/analytics/platform**

Statistiques par plateforme

**GET /api/settings**

Paramètres du widget

**PUT /api/settings**

Mettre à jour les paramètres

---

## 🎨 Personnalisation

### Thèmes

#### Thème Clair (Défaut)

```css
:root {
  --bg-color: #ffffff;
  --text-color: #1f2937;
  --header-bg: #f9fafb;
  --border-color: #e5e7eb;
  --highlight-color: #3b82f6;
}
```

#### Thème Sombre

```css
:root {
  --bg-color: #1f2937;
  --text-color: #f9fafb;
  --header-bg: #111827;
  --border-color: #374151;
  --highlight-color: #60a5fa;
}
```

#### Thème Personnalisé

Via l'interface admin :
1. **Paramètres → Apparence**
2. Sélectionnez "Personnalisé"
3. Choisissez vos couleurs

Ou via code :

```javascript
HashMyTag.init({
  apiKey: 'hmt_...',
  theme: 'custom',
  colors: {
    primary: '#FF6B6B',
    secondary: '#4ECDC4',
    background: '#FFFFFF',
    text: '#2D3436'
  }
});
```

### CSS Personnalisé

Ajoutez votre propre CSS :

```html
<style>
  .hashmytag-post {
    border-radius: 20px !important;
  }
  
  .post-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
  }
</style>
```

### Animations Personnalisées

Modifiez les animations :

```css
@keyframes customEntry {
  from {
    opacity: 0;
    transform: scale(0.8) rotate(-5deg);
  }
  to {
    opacity: 1;
    transform: scale(1) rotate(0deg);
  }
}

.hashmytag-post {
  animation: customEntry 0.6s ease-out !important;
}
```

---

## 🛠️ Commandes Artisan

### Gestion des Tenants

**Créer un nouveau tenant**
```bash
php artisan tenant:create domain.com "Nom Client" email@client.com --password=secret
```

**Sortie :**
```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Tenant Details:
Domain: domain.com
Email: email@client.com
Password: secret
API Key: hmt_...
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

### Synchronisation des Flux

**Synchroniser tous les flux**
```bash
php artisan feeds:sync
```

**Synchroniser un tenant spécifique**
```bash
php artisan feeds:sync --tenant=1
```

### Nettoyage

**Nettoyer les anciennes analytics**
```bash
php artisan analytics:clean --days=90
```

### Scheduler

Configurez le cron job :

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🚀 Déploiement

### Production Checklist

- [ ] `APP_DEBUG=false` dans `.env`
- [ ] `APP_ENV=production`
- [ ] Configurer les clés Stripe production
- [ ] Configurer les webhooks Stripe
- [ ] Optimiser l'application : `php artisan optimize`
- [ ] Compiler les assets : `npm run build`
- [ ] Configurer le cache : `php artisan config:cache`
- [ ] Configurer les routes : `php artisan route:cache`
- [ ] Configurer les vues : `php artisan view:cache`
- [ ] Activer le scheduler cron
- [ ] Configurer les backups de base de données
- [ ] Activer HTTPS
- [ ] Configurer le CDN pour les assets

### Serveur Recommandé

**Nginx Configuration**

```nginx
server {
    listen 80;
    server_name hashmytag.com;
    root /var/www/hashmytag/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Performance

**Optimisations recommandées :**

1. **Cache Redis**
```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

2. **Queue Workers**
```bash
php artisan queue:work --tries=3
```

3. **Horizon (optional)**
```bash
composer require laravel/horizon
php artisan horizon:install
php artisan horizon
```

---

## 🔧 Troubleshooting

### Problèmes Courants

#### "API Key invalid"

**Solution :**
- Vérifiez que l'API key est correcte
- Format : `hmt_...`
- Récupérez-la depuis le dashboard ou la commande `tenant:create`

#### "Instagram API error"

**Solutions :**
- Vérifiez que votre access token est valide
- Les tokens Instagram expirent après 60 jours
- Régénérez un nouveau token depuis Facebook Developers

#### "Posts not loading"

**Solutions :**
```bash
# Vider le cache
php artisan cache:clear

# Synchroniser manuellement
php artisan feeds:sync

# Vérifier les logs
tail -f storage/logs/laravel.log
```

#### "Widget not displaying"

**Solutions :**
- Vérifiez que le script est chargé : `https://yourdomain.com/widget.js`
- Ouvrez la console du navigateur (F12) pour voir les erreurs
- Vérifiez que l'API key est valide
- Vérifiez que le div `#hashmytag-wall` existe

### Logs

**Consulter les logs :**
```bash
tail -f storage/logs/laravel.log
```

**Activer le debug mode :**
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

### Support

- **Email** : support@hashmytag.com
- **Documentation** : https://docs.hashmytag.com
- **Discord** : https://discord.gg/hashmytag
- **GitHub Issues** : https://github.com/your-org/hashmytag/issues

---

## 📝 Exemples Complets

### Exemple 1 : Boutique E-commerce

```html
<!-- Affichage des posts Instagram avec #votreboutique -->
<div class="container">
  <h2>Nos clients partagent</h2>
  <div id="hashmytag-wall"></div>
</div>

<script src="https://hashmytag.com/widget.js"></script>
<script>
  HashMyTag.init({
    apiKey: 'hmt_...',
    theme: 'light',
    direction: 'horizontal',
    speed: 'medium',
    gamification: true,
    fullscreen: false,
    postsPerView: 4
  });
</script>
```

### Exemple 2 : Événement / Conférence

```html
<!-- Mur social live pendant l'événement -->
<div id="hashmytag-wall" style="height: 100vh;"></div>

<script src="https://hashmytag.com/widget.js"></script>
<script>
  HashMyTag.init({
    apiKey: 'hmt_...',
    theme: 'dark',
    direction: 'vertical',
    speed: 'fast',
    gamification: true,
    fullscreen: true,
    autoplay: true
  });
  
  // Auto-fullscreen
  setTimeout(() => {
    document.querySelector('.hashmytag-fullscreen-btn').click();
  }, 500);
</script>
```

### Exemple 3 : Restaurant avec Avis

```html
<!-- Affichage des avis Google Reviews -->
<div class="reviews-section">
  <h2>Vos avis nous motivent ⭐</h2>
  <div id="hashmytag-wall"></div>
</div>

<script src="https://hashmytag.com/widget.js"></script>
<script>
  HashMyTag.init({
    apiKey: 'hmt_...',
    theme: 'custom',
    colors: {
      primary: '#FF6B6B',
      secondary: '#4ECDC4',
      background: '#FFF5E4',
      text: '#2D3436'
    },
    direction: 'horizontal',
    speed: 'slow',
    gamification: false,
    postsPerView: 3
  });
</script>
```

---

**Version :** 1.0.0  
**Last Updated :** 2025  
**License :** MIT

