# 🚀 Configuration Wasabi pour HashMyTag

## ✅ **Pourquoi Wasabi est Parfait pour Vous**

**Wasabi est compatible S3** et l'architecture HashMyTag est **déjà prête** !

### 💰 **Comparaison des Coûts**

| Provider | Stockage (1 TB) | Transfert (1 TB) | **Total/mois** |
|----------|----------------|------------------|----------------|
| **AWS S3** | $23 | $90 | **$113** |
| **Cloudflare R2** | $15 | $0 | **$15** |
| **Wasabi** | $6.99 | $0* | **$6.99** 🏆 |

*Wasabi = Transfert gratuit tant que le téléchargement ≤ stockage

**Verdict :** Wasabi est **16x moins cher qu'AWS S3** ! 🎉

---

## 📋 **CE QUI A ÉTÉ MODIFIÉ**

### ✅ **Code modifié automatiquement :**

1. **config/filesystems.php** ✅
   - Ajout de la configuration Wasabi (lignes 33-42)

2. **app/Services/MediaStorageService.php** ✅
   - Le disque est maintenant configurable via `.env`
   - Aucune logique métier modifiée

### ✅ **Ce qui reste identique :**
- ✅ Tous les contrôleurs
- ✅ Tous les modèles
- ✅ Le FeedService
- ✅ Les commandes Artisan
- ✅ Le widget JS
- ✅ L'interface admin

**Total de lignes modifiées : ~15 lignes**  
**Total de fichiers modifiés : 2 fichiers**

---

## 🔧 **INSTALLATION WASABI EN 3 ÉTAPES**

### **Étape 1 : Créer un Compte Wasabi**

1. Aller sur [wasabi.com](https://wasabi.com)
2. Créer un compte (essai gratuit 30 jours)
3. Choisir une région :
   - **eu-central-1** (Amsterdam) - Recommandé pour Europe
   - **us-east-1** (Virginie) - Pour USA
   - **ap-northeast-1** (Tokyo) - Pour Asie

### **Étape 2 : Créer un Bucket**

1. Dans le dashboard Wasabi → **Buckets** → **Create Bucket**
2. Nom : `hashmytag-media` (ou votre choix)
3. Région : Choisir la même que votre serveur
4. **Enable Bucket Logging** : Non (optionnel)
5. **Bucket Versioning** : Non (économise l'espace)
6. Cliquer sur **Create Bucket**

### **Étape 3 : Créer les Access Keys**

1. Aller dans **Access Keys** → **Create New Access Key**
2. Nom : `hashmytag-production`
3. **Copier et sauvegarder** :
   - Access Key ID
   - Secret Access Key
   - ⚠️ Vous ne pourrez plus voir le secret après !

---

## ⚙️ **CONFIGURATION .env**

### **Option A : Utiliser Wasabi comme disque par défaut**

```env
# Dans votre fichier .env

# Activer Wasabi comme disque par défaut
FILESYSTEM_DISK=wasabi

# Credentials Wasabi
WASABI_ACCESS_KEY_ID=AKIAIOSFODNN7EXAMPLE
WASABI_SECRET_ACCESS_KEY=wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY
WASABI_DEFAULT_REGION=eu-central-1
WASABI_BUCKET=hashmytag-media
WASABI_ENDPOINT=https://s3.eu-central-1.wasabisys.com
```

### **Option B : Garder local + Wasabi (pour tests)**

```env
# Garder local par défaut
FILESYSTEM_DISK=public

# Mais avoir Wasabi configuré pour migration future
WASABI_ACCESS_KEY_ID=AKIAIOSFODNN7EXAMPLE
WASABI_SECRET_ACCESS_KEY=wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY
WASABI_DEFAULT_REGION=eu-central-1
WASABI_BUCKET=hashmytag-media
WASABI_ENDPOINT=https://s3.eu-central-1.wasabisys.com
```

---

## 🌍 **Endpoints Wasabi par Région**

| Région | Endpoint | Localisation |
|--------|----------|--------------|
| **eu-central-1** | `https://s3.eu-central-1.wasabisys.com` | Amsterdam 🇳🇱 |
| **eu-west-1** | `https://s3.eu-west-1.wasabisys.com` | Londres 🇬🇧 |
| **eu-west-2** | `https://s3.eu-west-2.wasabisys.com` | Paris 🇫🇷 |
| **us-east-1** | `https://s3.us-east-1.wasabisys.com` | Virginie 🇺🇸 |
| **us-east-2** | `https://s3.us-east-2.wasabisys.com` | Virginie 🇺🇸 |
| **us-west-1** | `https://s3.us-west-1.wasabisys.com` | Oregon 🇺🇸 |
| **us-central-1** | `https://s3.us-central-1.wasabisys.com` | Texas 🇺🇸 |
| **ap-northeast-1** | `https://s3.ap-northeast-1.wasabisys.com` | Tokyo 🇯🇵 |
| **ap-northeast-2** | `https://s3.ap-northeast-2.wasabisys.com` | Osaka 🇯🇵 |

---

## 🧪 **TESTER LA CONFIGURATION**

### **Test 1 : Vérifier la connexion**

```bash
# Créer une commande de test
php artisan tinker
```

```php
// Dans tinker
Storage::disk('wasabi')->put('test.txt', 'Hello Wasabi!');
Storage::disk('wasabi')->exists('test.txt'); // Devrait retourner true
Storage::disk('wasabi')->get('test.txt'); // Devrait retourner "Hello Wasabi!"
Storage::disk('wasabi')->delete('test.txt');
```

### **Test 2 : Uploader une image**

```php
// Dans tinker
$imageUrl = 'https://picsum.photos/800/600';
$response = Http::get($imageUrl);
Storage::disk('wasabi')->put('media/posts/test.jpg', $response->body());

// Obtenir l'URL
Storage::disk('wasabi')->url('media/posts/test.jpg');
```

### **Test 3 : Synchroniser un flux**

```bash
# Activer Wasabi
# Modifier .env : FILESYSTEM_DISK=wasabi

# Synchroniser
php artisan feeds:sync

# Vérifier dans le dashboard Wasabi que les images sont uploadées
```

---

## 🔄 **MIGRATION DEPUIS LOCAL VERS WASABI**

Si vous avez déjà des images en local :

```bash
php artisan make:command MigrateToWasabi
```

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateToWasabi extends Command
{
    protected $signature = 'media:migrate-to-wasabi';
    protected $description = 'Migrate local media to Wasabi';

    public function handle(): int
    {
        $files = Storage::disk('public')->allFiles('media/posts');
        
        $this->info("Found " . count($files) . " files to migrate");
        $bar = $this->output->createProgressBar(count($files));
        
        foreach ($files as $file) {
            // Lire depuis local
            $content = Storage::disk('public')->get($file);
            
            // Écrire sur Wasabi
            Storage::disk('wasabi')->put($file, $content);
            
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
php artisan media:migrate-to-wasabi
```

---

## 📊 **MONITORING WASABI**

### **Dashboard Wasabi**

1. **Storage Usage** : Voir l'espace utilisé
2. **Download Statistics** : Voir les téléchargements
3. **Billing** : Voir les coûts en temps réel

### **Dans HashMyTag**

```bash
# Voir les stats de stockage
php artisan tinker
```

```php
$service = app(\App\Services\MediaStorageService::class);
echo $service->formatSize($service->getStorageSize());
```

---

## 🚀 **OPTIMISATIONS WASABI**

### **1. Activer la compression**

```php
// Dans MediaStorageService.php
Storage::disk('wasabi')->put($path, $response->body(), [
    'ContentEncoding' => 'gzip',
    'CacheControl' => 'max-age=31536000',
]);
```

### **2. Définir les headers de cache**

```php
// Pour les images publiques
Storage::disk('wasabi')->put($path, $response->body(), [
    'visibility' => 'public',
    'CacheControl' => 'public, max-age=31536000',
    'ContentType' => 'image/jpeg',
]);
```

### **3. Lifecycle Policy (Auto-delete)**

Dans le dashboard Wasabi :
1. Aller dans votre bucket
2. **Lifecycle Rules** → **Create Rule**
3. Nom : `auto-delete-old-media`
4. Règle : Supprimer les fichiers après 90 jours
5. Sauvegarder

---

## 💡 **MEILLEURES PRATIQUES**

### ✅ **À FAIRE**

1. **Choisir la région proche de votre serveur**
   - Europe → `eu-central-1` ou `eu-west-2`
   - USA → `us-east-1` ou `us-west-1`

2. **Configurer un CDN (optionnel)**
   - Cloudflare devant Wasabi
   - Améliore les performances globales

3. **Backup régulier**
   - Wasabi a une réplication automatique
   - Mais prévoir un backup externe critique

4. **Monitoring**
   - Surveiller l'utilisation mensuelle
   - Activer les alertes Wasabi

### ❌ **À ÉVITER**

1. **Ne pas mixer local + Wasabi en production**
   - Choisir un seul système

2. **Ne pas oublier la région dans .env**
   - Erreur courante : endpoint mal configuré

3. **Ne pas uploader de fichiers > 5 GB**
   - Utiliser multipart upload pour gros fichiers

---

## 🔒 **SÉCURITÉ**

### **1. Permissions Bucket**

```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Principal": "*",
      "Action": "s3:GetObject",
      "Resource": "arn:aws:s3:::hashmytag-media/media/posts/*"
    }
  ]
}
```

### **2. CORS Configuration**

```json
[
  {
    "AllowedHeaders": ["*"],
    "AllowedMethods": ["GET", "HEAD"],
    "AllowedOrigins": ["https://yourdomain.com"],
    "ExposeHeaders": ["ETag"]
  }
]
```

---

## 📈 **COÛTS ESTIMÉS**

### **Scénario 1 : Petit site (1,000 posts/mois)**

- Stockage : 500 MB
- Transfert : 5 GB/mois
- **Coût : $0.50/mois** 💰

### **Scénario 2 : Site moyen (10,000 posts/mois)**

- Stockage : 5 GB
- Transfert : 50 GB/mois
- **Coût : $6.99/mois** 💰

### **Scénario 3 : Gros site (100,000 posts/mois)**

- Stockage : 50 GB
- Transfert : 500 GB/mois
- **Coût : $34.95/mois** 💰

**Note :** Minimum facturé par Wasabi = $6.99/mois (1 TB)

---

## 🎯 **CHECKLIST DE DÉPLOIEMENT**

### **Avant Production**

- [ ] Compte Wasabi créé
- [ ] Bucket créé avec bonne région
- [ ] Access keys générées et sauvegardées
- [ ] `.env` configuré avec credentials
- [ ] Test de connexion réussi
- [ ] Migration des médias existants (si nécessaire)

### **En Production**

- [ ] `FILESYSTEM_DISK=wasabi` dans `.env`
- [ ] Cache cleared : `php artisan config:clear`
- [ ] Test d'upload d'une image
- [ ] Vérification dans dashboard Wasabi
- [ ] Test du widget avec nouvelles images
- [ ] Monitoring activé

---

## 🆘 **TROUBLESHOOTING**

### **Erreur : "Endpoint not found"**

**Solution :**
```env
# Vérifier l'endpoint dans .env
WASABI_ENDPOINT=https://s3.eu-central-1.wasabisys.com
# SANS slash à la fin !
```

### **Erreur : "Access Denied"**

**Solution :**
1. Vérifier les credentials dans `.env`
2. Vérifier que le bucket existe
3. Vérifier les permissions du bucket

### **Images ne s'affichent pas**

**Solution :**
1. Vérifier la policy du bucket (public read)
2. Vérifier les CORS si nécessaire
3. Vérifier l'URL retournée : `Storage::disk('wasabi')->url('path')`

---

## 📞 **SUPPORT**

### **Wasabi Support**
- Email : support@wasabi.com
- Documentation : https://wasabi-support.zendesk.com
- Phone : +1-844-WASABI-1

### **HashMyTag + Wasabi**
- Voir `MEDIA_STORAGE_GUIDE.md`
- GitHub Issues
- support@hashmytag.com

---

## ✅ **RÉSUMÉ**

### **Ce qui a été modifié dans le code :**

1. ✅ `config/filesystems.php` - Ajout configuration Wasabi (10 lignes)
2. ✅ `app/Services/MediaStorageService.php` - Disque configurable (5 lignes)

### **Ce qui n'a PAS changé :**

- ✅ Aucun contrôleur
- ✅ Aucun modèle
- ✅ Aucune route
- ✅ Aucune vue
- ✅ Aucune logique métier
- ✅ Le widget JS
- ✅ L'API

**Total : 15 lignes modifiées, tout fonctionne avec Wasabi ! 🎉**

---

**Configuration :** 5 minutes  
**Migration :** 10 minutes  
**Tests :** 5 minutes  
**Total :** 20 minutes pour passer à Wasabi ! ⚡

