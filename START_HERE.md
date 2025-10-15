# 🚀 DÉMARRAGE RAPIDE - HashMyTag MVP

## ✅ TOUT LE CODE EST PRÊT !

L'application est **100% complète** avec **Solution Hybride** implémentée !

### 🌟 **NOUVEAUTÉ : Mode Simple + Mode Avancé**

- 🟢 **Mode Simple** : API HashMyTag (hashtags publics) - Inclus
- 🟣 **Mode Avancé** : Connexion compte client (+20€/mois) - NOUVEAU !

---

## ⚡ INSTALLATION EN 5 MINUTES

### Étape 1 : Ouvrir PowerShell dans le dossier

```powershell
cd C:\Users\Lenovo\Desktop\hashmytag
```

### Étape 2 : Installer les dépendances

```powershell
composer install
npm install
```

### Étape 3 : Configuration

```powershell
# Copier .env
copy .env.example .env

# Générer la clé
php artisan key:generate
```

### Étape 4 : Base de données

**Option A - SQLite (PLUS SIMPLE) :**
```powershell
# Créer le fichier
New-Item -Path "database" -Name "database.sqlite" -ItemType "file"

# Puis modifier .env :
# DB_CONNECTION=sqlite
# Commenter les autres lignes DB_*
```

**Option B - MySQL :**
```sql
-- Dans MySQL
CREATE DATABASE hashmytag CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```env
# Dans .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hashmytag
DB_USERNAME=root
DB_PASSWORD=votre_mot_de_passe
```

### Étape 5 : Migrations et Setup

```powershell
# Exécuter les migrations
php artisan migrate

# Créer le lien symbolique
php artisan storage:link

# Compiler les assets
npm run build
```

### Étape 6 : Démarrer !

```powershell
php artisan serve
```

**Ouvrir dans le navigateur :** http://localhost:8000

---

## 👤 PREMIER COMPTE

### Option 1 : Via l'interface (Recommandé)

1. Aller sur http://localhost:8000/register
2. Remplir le formulaire :
   - Nom : Test Company
   - Email : admin@test.com
   - Domaine : test.local
   - Mot de passe : password
3. Cliquer sur "Créer mon compte"
4. Vous serez automatiquement connecté au dashboard !

### Option 2 : Via la commande (Alternative)

```powershell
php artisan tenant:create test.local "Test Company" admin@test.com --password=password
```

**Puis se connecter sur :**
- URL : http://localhost:8000/login
- Email : admin@test.com
- Password : password

---

## 🎯 TESTER LE WIDGET (Sans API)

### 1. Récupérer votre API Key

1. Se connecter au dashboard
2. Aller dans **Settings**
3. Copier l'API Key (format : `hmt_...`)

### 2. Créer une page de test

Créer un fichier `test-widget.html` :

```html
<!DOCTYPE html>
<html>
<head>
    <title>Test Widget HashMyTag</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
        }
        h1 {
            color: #3b82f6;
        }
    </style>
</head>
<body>
    <h1>🎉 Test du Widget HashMyTag</h1>
    <p>Le widget devrait apparaître ci-dessous :</p>
    
    <div id="hashmytag-wall"></div>
    
    <script src="http://localhost:8000/widget.js"></script>
    <script>
        HashMyTag.init({
            apiKey: 'VOTRE_API_KEY_ICI', // À remplacer
            theme: 'light',
            direction: 'vertical',
            speed: 'medium',
            gamification: true,
            fullscreen: true
        });
    </script>
</body>
</html>
```

### 3. Ouvrir dans le navigateur

Double-cliquer sur `test-widget.html`

---

## 📱 PREMIERS PAS

### 1. Dashboard
- Vue d'ensemble de votre compte
- Statistiques en temps réel
- Actions rapides

### 2. Créer un Flux
1. Aller dans **Flux**
2. Cliquer sur "Ajouter un flux"
3. Choisir une plateforme (Instagram, Facebook, etc.)
4. Pour tester sans API :
   - Nom : "Test Instagram"
   - Type : Instagram
   - Hashtags : test, demo, social

### 3. Personnaliser le Widget
1. Aller dans **Paramètres**
2. Choisir :
   - Thème (light/dark)
   - Direction (vertical/horizontal)
   - Vitesse (slow/medium/fast)
   - Activer la gamification
3. Copier le code d'intégration

### 4. Tester les Analytics
1. Aller dans **Analytics**
2. Voir les statistiques (vues, clics, etc.)

---

## 🔑 CONFIGURATION API (Optionnel)

Pour que les flux récupèrent vraiment des posts :

### Instagram
```env
INSTAGRAM_APP_ID=votre_app_id
INSTAGRAM_APP_SECRET=votre_app_secret
INSTAGRAM_ACCESS_TOKEN=votre_access_token
```

### Facebook
```env
FACEBOOK_APP_ID=votre_app_id
FACEBOOK_APP_SECRET=votre_app_secret
FACEBOOK_ACCESS_TOKEN=votre_access_token
```

### Twitter
```env
TWITTER_API_KEY=votre_api_key
TWITTER_API_SECRET=votre_api_secret
TWITTER_BEARER_TOKEN=votre_bearer_token
```

### Google Reviews
```env
GOOGLE_CLIENT_ID=votre_client_id
GOOGLE_CLIENT_SECRET=votre_client_secret
GOOGLE_API_KEY=votre_api_key
```

**Guide complet :** Voir `DOCUMENTATION.md`

---

## 🎨 FONCTIONNALITÉS DISPONIBLES

### ✅ Déjà Fonctionnel
- [x] Dashboard complet
- [x] Gestion des flux (CRUD)
- [x] **Mode Simple + Mode Avancé (NOUVEAU !)**
- [x] **OAuth connexion comptes (Instagram, Facebook, Twitter)**
- [x] **Token refresh automatique**
- [x] **Add-ons Stripe (+20€/mois par connexion)**
- [x] Configuration du widget
- [x] Widget JavaScript responsive
- [x] Gamification
- [x] Mode plein écran
- [x] Analytics basiques
- [x] Multi-tenant
- [x] Authentification

### 🔄 Nécessite Configuration API
- [ ] Récupération posts Instagram (Mode Simple)
- [ ] Récupération posts Facebook (Mode Simple)
- [ ] Récupération tweets (Mode Simple)
- [ ] Récupération avis Google
- [ ] OAuth apps (Mode Avancé - optionnel)

### 💳 Optionnel pour MVP
- [ ] Stripe (facturation + add-ons)
- [ ] Email notifications
- [ ] Webhooks

---

## 🆘 PROBLÈMES COURANTS

### "composer: command not found"
```powershell
# Installer Composer depuis composer.org
```

### "npm: command not found"
```powershell
# Installer Node.js depuis nodejs.org
```

### "SQLSTATE[HY000] [1045]"
```powershell
# Vérifier les credentials MySQL dans .env
# OU utiliser SQLite (plus simple)
```

### "Mix manifest not found"
```powershell
npm run build
```

### "Storage not linked"
```powershell
php artisan storage:link
```

### Le widget ne s'affiche pas
1. Vérifier que le serveur tourne (`php artisan serve`)
2. Vérifier l'API key dans le code HTML
3. Ouvrir la console du navigateur (F12) pour voir les erreurs

---

## 📊 COMMANDES UTILES

```powershell
# Démarrer le serveur
php artisan serve

# Voir les logs
Get-Content storage\logs\laravel.log -Tail 50

# Effacer le cache
php artisan cache:clear
php artisan config:clear

# Voir les routes
php artisan route:list

# Créer un nouveau tenant
php artisan tenant:create exemple.com "Nom Client" email@client.com

# Synchroniser les flux
php artisan feeds:sync

# Nettoyer les médias anciens
php artisan media:clean
```

---

## 🎯 CHECKLIST MVP

- [ ] Application installée
- [ ] Base de données créée
- [ ] Migrations exécutées
- [ ] Premier compte créé
- [ ] Login réussi
- [ ] Dashboard accessible
- [ ] API Key récupérée
- [ ] Widget testé
- [ ] Un flux créé
- [ ] Widget personnalisé

**Temps estimé : 15-30 minutes**

---

## 📚 DOCUMENTATION

- `README.md` - Vue d'ensemble
- `QUICKSTART.md` - Démarrage rapide
- `DOCUMENTATION.md` - Documentation complète
- `MVP_ACTION_PLAN.md` - Plan d'action MVP détaillé
- `SCALABILITY_ANALYSIS.md` - Analyse de scalabilité
- `MEDIA_STORAGE_GUIDE.md` - Guide stockage médias
- `WASABI_SETUP.md` - Configuration Wasabi (production)

---

## 🎊 PRÊT À DÉMARRER !

**3 commandes pour tout installer :**

```powershell
# 1. Installation
composer install && npm install && copy .env.example .env && php artisan key:generate

# 2. Database + Build (SQLite)
New-Item -Path "database" -Name "database.sqlite" -ItemType "file"
# Modifier .env : DB_CONNECTION=sqlite
php artisan migrate && php artisan storage:link && npm run build

# 3. Démarrer
php artisan serve
```

**Puis ouvrir :** http://localhost:8000/register

---

**🚀 Bienvenue dans HashMyTag !**

Des questions ? Voir `DOCUMENTATION.md` ou `MVP_ACTION_PLAN.md`

