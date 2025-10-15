# ⚡ Démarrage Rapide - HashMyTag v1.2.0

## 🎯 En 5 Minutes

### 1️⃣ Installation

```bash
# Cloner et installer
git clone https://github.com/your-org/hashmytag.git
cd hashmytag
composer install && npm install

# Configurer
cp .env.example .env
php artisan key:generate

# Base de données (inclut gamification v1.2)
php artisan migrate

# Seeder badges gamification (15 badges)
php artisan db:seed --class=BadgeSeeder

# Compiler
npm run build

# Queue workers (gamification)
php artisan queue:work &
```

### 2️⃣ Créer votre compte

```bash
php artisan tenant:create monsite.com "Ma Société" admin@monsite.com
```

Notez votre **API Key** : `hmt_...`

### 3️⃣ Configurer un flux

```bash
# Démarrer le serveur
php artisan serve

# Aller sur http://localhost:8000
# Se connecter avec vos identifiants
# Créer votre premier flux dans "Flux"
```

### 4️⃣ Intégrer le widget

Copiez ce code sur votre site :

```html
<div id="hashmytag-wall"></div>
<script src="http://localhost:8000/widget.js"></script>
<script>
  HashMyTag.init({
    apiKey: 'hmt_VOTRE_CLE_API'
  });
</script>
```

### 5️⃣ C'est fait ! 🎉

Votre mur social est maintenant live !

## 🚀 Prochaines Étapes

### Configurer les API Sociales

**Instagram** (5 min)
1. Aller sur [Facebook Developers](https://developers.facebook.com)
2. Créer une app
3. Activer Instagram Basic Display
4. Copier les credentials dans `.env`

**Facebook** (3 min)
1. Même app que Instagram
2. Activer Facebook Graph API
3. Copier l'access token dans `.env`

**Twitter** (10 min)
1. Créer une app sur [Twitter Developer](https://developer.twitter.com)
2. Demander l'accès Elevated
3. Copier les clés dans `.env`

**Google Reviews** (5 min)
1. Créer un projet [Google Cloud](https://console.cloud.google.com)
2. Activer Google My Business API
3. Copier les credentials dans `.env`

### Personnaliser le Widget

```javascript
HashMyTag.init({
  apiKey: 'hmt_...',
  theme: 'light',           // light, dark, custom
  direction: 'vertical',    // vertical, horizontal
  speed: 'medium',          // slow, medium, fast
  gamification: true,       // badges et animations
  fullscreen: true,         // bouton plein écran
  autoplay: true,           // défilement auto
  postsPerView: 3          // nombre de posts visibles
});
```

### Configurer Stripe (Production)

1. Créer un compte [Stripe](https://stripe.com)
2. Créer les produits et prix
3. Copier les clés dans `.env`
4. Configurer le webhook : `https://votresite.com/stripe/webhook`

## 📚 Ressources

- [Documentation Complète](DOCUMENTATION.md)
- [Guide d'Installation](INSTALLATION.md)
- [Contribuer](CONTRIBUTING.md)
- [Support](mailto:support@hashmytag.com)

## 💡 Exemples

### E-commerce
```html
<div id="hashmytag-wall"></div>
<script src="https://votresite.com/widget.js"></script>
<script>
  HashMyTag.init({
    apiKey: 'hmt_...',
    theme: 'light',
    direction: 'horizontal',
    postsPerView: 4
  });
</script>
```

### Événement / Conférence
```html
<div id="hashmytag-wall" style="height:100vh"></div>
<script src="https://votresite.com/widget.js"></script>
<script>
  HashMyTag.init({
    apiKey: 'hmt_...',
    theme: 'dark',
    direction: 'vertical',
    speed: 'fast',
    gamification: true
  });
</script>
```

### Restaurant
```html
<div id="hashmytag-wall"></div>
<script src="https://votresite.com/widget.js"></script>
<script>
  HashMyTag.init({
    apiKey: 'hmt_...',
    theme: 'custom',
    colors: {
      primary: '#FF6B6B',
      background: '#FFF5E4'
    },
    speed: 'slow'
  });
</script>
```

## ❓ Problèmes ?

### Widget ne s'affiche pas

```bash
# Vérifier les logs
tail -f storage/logs/laravel.log

# Vider le cache
php artisan cache:clear

# Synchroniser les flux
php artisan feeds:sync
```

### API Key invalide

- Récupérer votre API key : Dashboard → Paramètres
- Format : `hmt_...`
- Vérifier qu'elle est bien dans le code

### Posts ne se chargent pas

```bash
# Synchroniser manuellement
php artisan feeds:sync

# Vérifier la configuration des flux
# Dashboard → Flux
```

## 🎉 Félicitations !

Votre mur social live est maintenant opérationnel !

**Besoin d'aide ?** → support@hashmytag.com

