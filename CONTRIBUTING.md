# 🤝 Contributing to HashMyTag

Merci de contribuer à HashMyTag ! Voici comment vous pouvez aider.

## Comment Contribuer

### 1. Fork & Clone
```bash
git clone https://github.com/your-username/hashmytag.git
cd hashmytag
git checkout -b feature/ma-nouvelle-fonctionnalite
```

### 2. Installer les dépendances
```bash
composer install
npm install
```

### 3. Faire vos modifications
- Suivez les conventions de code PSR-12 pour PHP
- Utilisez ESLint pour JavaScript
- Écrivez des tests si possible

### 4. Tester
```bash
php artisan test
npm run test
```

### 5. Commit
```bash
git add .
git commit -m "feat: ajouter support pour TikTok"
```

### 6. Push & Pull Request
```bash
git push origin feature/ma-nouvelle-fonctionnalite
```

Créez ensuite une Pull Request sur GitHub.

## Convention de Commits

- `feat:` Nouvelle fonctionnalité
- `fix:` Correction de bug
- `docs:` Documentation
- `style:` Formatting
- `refactor:` Refactoring
- `test:` Tests
- `chore:` Maintenance

## Ajouter un Nouveau Flux

### 1. Créer le Provider

```php
// app/Services/Feeds/TikTokFeed.php
<?php

namespace App\Services\Feeds;

use App\Contracts\FeedProvider;

class TikTokFeed implements FeedProvider
{
    public function fetch(array $config): array
    {
        // Votre logique
    }
    
    public function normalize($data): array
    {
        // Normaliser les données
    }
    
    public function validateConfig(array $config): bool
    {
        // Valider la config
    }
    
    public function getPlatformName(): string
    {
        return 'TikTok';
    }
}
```

### 2. Enregistrer dans config/feeds.php

```php
'providers' => [
    'tiktok' => \App\Services\Feeds\TikTokFeed::class,
],
```

### 3. Ajouter les credentials dans .env.example

```env
TIKTOK_APP_ID=
TIKTOK_APP_SECRET=
TIKTOK_ACCESS_TOKEN=
```

### 4. Documenter

Ajoutez la documentation dans DOCUMENTATION.md

## Code Style

### PHP
```bash
./vendor/bin/pint
```

### JavaScript
```bash
npm run lint
```

## Tests

```bash
# Tests unitaires
php artisan test

# Tests spécifiques
php artisan test --filter=FeedTest
```

## Questions ?

Ouvrez une issue sur GitHub ou contactez-nous sur Discord.

Merci ! 🙏

