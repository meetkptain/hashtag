# 📸 Guide de Stockage des Médias - HashMyTag

## 🎯 Stratégies de Stockage

### **Option 1 : URLs Uniquement** (Actuel)

**✅ Avantages :**
- Pas de stockage nécessaire
- Simplicité maximale
- Aucun coût de stockage

**❌ Inconvénients :**
- URLs Instagram expirent après 24h
- Dépendance aux plateformes externes
- Si l'image est supprimée, elle disparaît

**📊 Recommandé pour :** Prototypes, tests, petits projets

---

### **Option 2 : Cache Local** (Recommandé) 🌟

**✅ Avantages :**
- Images toujours disponibles
- Performance optimale
- Contrôle complet
- Pas de dépendance externe

**❌ Inconvénients :**
- Nécessite de l'espace disque
- Maintenance requise (nettoyage)
- Bande passante pour téléchargement

**📊 Recommandé pour :** Production, applications professionnelles

---

### **Option 3 : CDN (AWS S3 / Cloudflare R2)** (Optimal)

**✅ Avantages :**
- Performance maximale (edge locations)
- Scalabilité illimitée
- Backup automatique
- Optimisation des images

**❌ Inconvénients :**
- Coût mensuel
- Configuration plus complexe

**📊 Recommandé pour :** Applications à fort trafic

---

## 🚀 **IMPLÉMENTATION**

### **1. Activer le Stockage Local**

```bash
# Créer le lien symbolique
php artisan storage:link
```

### **2. Modifier FeedService pour télécharger les images**

```php
// Dans app/Services/FeedService.php

protected function createOrUpdatePost(Feed $feed, array $data): bool
{
    // ... code existant ...
    
    // NOUVEAU : Télécharger et stocker les médias
    if (!empty($data['media'])) {
        $mediaService = app(MediaStorageService::class);
        $data['media'] = $mediaService->downloadMultiple(
            $data['media'], 
            $data['external_id']
        );
    }
    
    Post::create([
        'feed_id' => $feed->id,
        ...$data,
    ]);
    
    return true;
}
```

### **3. Configuration dans .env**

```env
# Stockage local (par défaut)
FILESYSTEM_DISK=public

# OU AWS S3
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=eu-west-1
AWS_BUCKET=hashmytag-media
AWS_URL=https://your-bucket.s3.amazonaws.com
```

### **4. Nettoyer les médias anciens**

```bash
# Nettoyer les fichiers > 90 jours
php artisan media:clean

# Personnaliser la durée
php artisan media:clean --days=30
```

### **5. Ajouter au Scheduler**

```php
// routes/console.php
Schedule::command('media:clean')->weekly();
```

---

## 📊 **COMPARAISON DES COÛTS**

### **Estimation pour 10,000 posts/mois**

| Solution | Stockage | Bande passante | Coût mensuel |
|----------|----------|----------------|--------------|
| **URLs uniquement** | 0 GB | 0 GB | **0€** |
| **Stockage local** | 5-10 GB | 50-100 GB | **0€** (serveur) |
| **AWS S3** | 10 GB | 100 GB | **~3€** |
| **Cloudflare R2** | 10 GB | Illimité | **~1€** |

---

## 🎨 **OPTIMISATION DES IMAGES**

### **1. Installer Intervention Image**

```bash
composer require intervention/image
```

### **2. Créer un Service d'Optimisation**

```php
<?php

namespace App\Services;

use Intervention\Image\Facades\Image;

class ImageOptimizer
{
    public function optimize(string $path, int $maxWidth = 1200): void
    {
        $image = Image::make($path);
        
        // Redimensionner si trop large
        if ($image->width() > $maxWidth) {
            $image->resize($maxWidth, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }
        
        // Optimiser la qualité
        $image->save($path, 85);
    }
}
```

### **3. Utiliser dans MediaStorageService**

```php
public function downloadAndStore(string $url, string $postId): ?string
{
    // ... téléchargement ...
    
    Storage::disk($this->disk)->put($path, $response->body());
    
    // NOUVEAU : Optimiser l'image
    $optimizer = app(ImageOptimizer::class);
    $optimizer->optimize(Storage::disk($this->disk)->path($path));
    
    return Storage::disk($this->disk)->url($path);
}
```

---

## 🌐 **CONFIGURATION CDN**

### **AWS S3 + CloudFront**

```env
# .env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=AKIA...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=eu-west-1
AWS_BUCKET=hashmytag-media
AWS_URL=https://d123456.cloudfront.net
```

### **Cloudflare R2** (Moins cher)

```bash
composer require league/flysystem-aws-s3-v3
```

```php
// config/filesystems.php
'r2' => [
    'driver' => 's3',
    'key' => env('R2_ACCESS_KEY_ID'),
    'secret' => env('R2_SECRET_ACCESS_KEY'),
    'region' => 'auto',
    'bucket' => env('R2_BUCKET'),
    'endpoint' => env('R2_ENDPOINT'),
    'use_path_style_endpoint' => false,
],
```

---

## 🔄 **MIGRATION DES DONNÉES EXISTANTES**

Si vous avez déjà des posts avec URLs, vous pouvez les migrer :

```bash
php artisan make:command MigrateMediaToLocal
```

```php
<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\MediaStorageService;
use Illuminate\Console\Command;

class MigrateMediaToLocal extends Command
{
    protected $signature = 'media:migrate';
    protected $description = 'Migrate existing media URLs to local storage';

    public function handle(MediaStorageService $mediaService): int
    {
        $posts = Post::whereNotNull('media')->get();
        $this->info("Found {$posts->count()} posts with media");
        
        $bar = $this->output->createProgressBar($posts->count());
        
        foreach ($posts as $post) {
            $media = $post->media;
            
            if (empty($media)) {
                continue;
            }
            
            // Télécharger les médias
            $downloaded = $mediaService->downloadMultiple($media, $post->external_id);
            
            // Mettre à jour le post
            $post->update(['media' => $downloaded]);
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info('✓ Migration completed!');
        
        return Command::SUCCESS;
    }
}
```

Puis exécuter :

```bash
php artisan media:migrate
```

---

## 📈 **MONITORING DU STOCKAGE**

### **Ajouter une page de statistiques**

```php
// app/Http/Controllers/Api/StorageController.php

public function stats(MediaStorageService $mediaService)
{
    return response()->json([
        'total_size' => $mediaService->getStorageSize(),
        'formatted_size' => $mediaService->formatSize($mediaService->getStorageSize()),
        'total_files' => count(Storage::disk('public')->allFiles('media/posts')),
    ]);
}
```

### **Dashboard Widget**

```vue
<!-- resources/js/Components/StorageStats.vue -->
<template>
  <div class="card">
    <h3 class="text-lg font-semibold mb-4">Stockage Média</h3>
    <div class="space-y-2">
      <div class="flex justify-between">
        <span>Fichiers</span>
        <span class="font-bold">{{ stats.total_files }}</span>
      </div>
      <div class="flex justify-between">
        <span>Espace utilisé</span>
        <span class="font-bold">{{ stats.formatted_size }}</span>
      </div>
    </div>
  </div>
</template>
```

---

## 💡 **RECOMMANDATIONS FINALES**

### **Pour Démarrer** (Phase 1)
1. ✅ Utiliser les URLs directement (simplicité)
2. ✅ Tester l'application
3. ✅ Valider le concept

### **Pour la Production** (Phase 2)
1. ✅ Activer le stockage local avec cache
2. ✅ Implémenter le nettoyage automatique
3. ✅ Optimiser les images (compression)

### **Pour Scaler** (Phase 3)
1. ✅ Migrer vers AWS S3 ou Cloudflare R2
2. ✅ Activer CDN (CloudFront / Cloudflare)
3. ✅ Implémenter le lazy loading avancé

---

## 🎯 **MA RECOMMANDATION**

Pour votre cas, je recommande **l'Option 2 (Cache Local)** :

```bash
# 1. Activer le stockage
php artisan storage:link

# 2. Modifier FeedService.php
# (ajouter le téléchargement des médias)

# 3. Configurer le nettoyage
# routes/console.php
Schedule::command('media:clean')->weekly();
```

**Pourquoi ?**
- ✅ Équilibre parfait performance/coût
- ✅ Indépendant des plateformes externes
- ✅ Facile à migrer vers S3 plus tard
- ✅ Aucun coût supplémentaire

**Espace disque estimé :**
- 1,000 posts = ~500 MB
- 10,000 posts = ~5 GB
- 100,000 posts = ~50 GB

---

**Questions ?** N'hésitez pas ! 💬

