# 🚀 Guide d'Installation Rapide - HashMyTag

## Installation en 10 minutes

### 1. Prérequis
```bash
# Vérifier les versions
php -v    # 8.1+
node -v   # 16+
mysql -V  # 8.0+
```

### 2. Installation
```bash
# Cloner et installer
git clone https://github.com/your-org/hashmytag.git
cd hashmytag
composer install
npm install

# Configuration
cp .env.example .env
php artisan key:generate
```

### 3. Base de données
```bash
# Créer la base de données
mysql -u root -p
CREATE DATABASE hashmytag;
exit;

# Exécuter les migrations
php artisan migrate
```

### 4. Compiler les assets
```bash
npm run build
```

### 5. Créer votre premier tenant
```bash
php artisan tenant:create example.com "Ma Société" admin@example.com --password=secret
```

### 6. Démarrer
```bash
php artisan serve
```

Accédez à http://localhost:8000

## Configuration API (optionnel)

### Instagram
```env
INSTAGRAM_APP_ID=
INSTAGRAM_APP_SECRET=
INSTAGRAM_ACCESS_TOKEN=
```

### Facebook
```env
FACEBOOK_APP_ID=
FACEBOOK_APP_SECRET=
FACEBOOK_ACCESS_TOKEN=
```

### Twitter
```env
TWITTER_API_KEY=
TWITTER_API_SECRET=
TWITTER_BEARER_TOKEN=
```

### Google Reviews
```env
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_API_KEY=
```

### Stripe
```env
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
```

## Premier flux

1. Connectez-vous au dashboard
2. Allez dans "Flux"
3. Créez votre premier flux
4. Récupérez le code widget
5. Intégrez sur votre site !

## Support

Questions ? support@hashmytag.com

