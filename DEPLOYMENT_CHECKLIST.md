# ✅ Checklist de Déploiement - HashMyTag

## 🎯 Phase Actuelle : Développement Local

### ✅ Configuration Actuelle (Validée)

**Stockage :** Local (filesystem)
```env
FILESYSTEM_DISK=public  # Par défaut, déjà configuré
```

**Avantages :**
- ✅ Simplicité maximale
- ✅ Aucun coût
- ✅ Développement rapide
- ✅ Aucune configuration externe requise

---

## 🚀 Quand Passer en Production

### 📊 Indicateurs de Migration

**Rester en local si :**
- 👤 < 100 utilisateurs
- 📸 < 5,000 posts
- 💾 < 5 GB de stockage
- 🌍 Trafic régional uniquement

**Migrer vers CDN (Wasabi) si :**
- 👥 > 100 utilisateurs
- 📸 > 5,000 posts
- 💾 > 5 GB de stockage
- 🌍 Trafic international
- ⚡ Performance critique

---

## 📋 Checklist Développement Local

### ✅ Configuration de Base

- [x] Composer packages installés
- [x] NPM packages installés
- [x] `.env` configuré
- [x] Base de données créée
- [x] Migrations exécutées
- [ ] Lien symbolique storage créé ⚠️ **À FAIRE**

```bash
# Important : Créer le lien symbolique
php artisan storage:link
```

### ✅ Configuration API (Optionnel pour tests)

**Instagram :**
- [ ] App créée sur Facebook Developers
- [ ] Access token récupéré
- [ ] Configuré dans `.env`

**Facebook :**
- [ ] App configurée
- [ ] Access token récupéré
- [ ] Configuré dans `.env`

**Twitter/X :**
- [ ] App créée sur Twitter Developer
- [ ] Bearer token récupéré
- [ ] Configuré dans `.env`

**Google Reviews :**
- [ ] Projet Google Cloud créé
- [ ] API activée
- [ ] Credentials récupérés
- [ ] Configurés dans `.env`

---

## 🔄 Migration Future vers Production

### Phase 1 : Préparation (1 semaine avant)

**Infrastructure :**
- [ ] Serveur production choisi (VPS, cloud)
- [ ] Domaine configuré avec DNS
- [ ] SSL/HTTPS activé
- [ ] Backup automatique configuré

**Base de données :**
- [ ] MySQL 8.0+ installé
- [ ] Backup automatique configuré
- [ ] Utilisateur dédié créé

**Configuration serveur :**
- [ ] Nginx/Apache configuré
- [ ] PHP 8.1+ avec extensions
- [ ] Redis installé (optionnel)
- [ ] Supervisor pour queues (optionnel)

### Phase 2 : Déploiement Application

**Code :**
```bash
# Sur le serveur
git clone https://github.com/your-org/hashmytag.git
cd hashmytag
composer install --optimize-autoloader --no-dev
npm install && npm run build
```

**Configuration :**
```bash
cp .env.example .env
# Éditer .env avec valeurs production
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Permissions :**
```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### Phase 3 : Migration vers CDN (Wasabi)

**Quand le moment arrive :**

1. **Créer compte Wasabi** (5 min)
2. **Créer bucket** (2 min)
3. **Générer access keys** (2 min)
4. **Configurer `.env`** (2 min)
   ```env
   FILESYSTEM_DISK=wasabi
   WASABI_ACCESS_KEY_ID=...
   WASABI_SECRET_ACCESS_KEY=...
   WASABI_BUCKET=hashmytag-media
   WASABI_ENDPOINT=https://s3.eu-central-1.wasabisys.com
   ```
5. **Migrer les médias existants** (10 min)
   ```bash
   php artisan media:migrate-to-wasabi
   ```
6. **Tester** (5 min)
7. **Activer** (1 min)

**Total : ~30 minutes de migration !**

---

## 🔧 Configuration `.env` par Environnement

### 🏠 Développement Local (Actuel)

```env
APP_NAME="HashMyTag Social Wall"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# Base de données locale
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hashmytag
DB_USERNAME=root
DB_PASSWORD=

# Stockage local (par défaut)
FILESYSTEM_DISK=public

# Stripe TEST mode
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...

# API sociales (optionnel pour tests)
INSTAGRAM_ACCESS_TOKEN=
FACEBOOK_ACCESS_TOKEN=
TWITTER_BEARER_TOKEN=
GOOGLE_API_KEY=
```

### 🚀 Production (Future)

```env
APP_NAME="HashMyTag Social Wall"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Base de données production
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hashmytag_prod
DB_USERNAME=hashmytag_user
DB_PASSWORD=STRONG_PASSWORD_HERE

# Stockage CDN (quand prêt)
FILESYSTEM_DISK=wasabi
WASABI_ACCESS_KEY_ID=...
WASABI_SECRET_ACCESS_KEY=...
WASABI_BUCKET=hashmytag-media
WASABI_ENDPOINT=https://s3.eu-central-1.wasabisys.com

# Stripe LIVE mode
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...

# API sociales (production)
INSTAGRAM_ACCESS_TOKEN=production_token
FACEBOOK_ACCESS_TOKEN=production_token
TWITTER_BEARER_TOKEN=production_token
GOOGLE_API_KEY=production_key

# Cache Redis (recommandé)
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Email (production)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=noreply@yourdomain.com
```

---

## 🧪 Tests Avant Production

### ✅ Tests Fonctionnels

**Backend :**
```bash
php artisan test
```

**Frontend :**
```bash
npm run test
```

**API :**
- [ ] GET /api/widget/config → 200
- [ ] GET /api/widget/posts → 200
- [ ] POST /api/feeds → 201
- [ ] GET /api/analytics → 200

**Widget :**
- [ ] Chargement sur page test
- [ ] Affichage des posts
- [ ] Mode plein écran
- [ ] Responsive mobile
- [ ] Gamification visible

### ✅ Tests de Performance

**Lighthouse :**
- [ ] Performance > 90
- [ ] Accessibility > 90
- [ ] Best Practices > 90
- [ ] SEO > 90

**Load Testing :**
```bash
# Avec Apache Bench
ab -n 1000 -c 10 http://localhost:8000/api/widget/posts
```

---

## 📊 Monitoring Production

### Outils Recommandés

**Application :**
- [ ] Laravel Telescope (dev)
- [ ] Laravel Horizon (queues)
- [ ] Sentry (errors)
- [ ] New Relic / DataDog (APM)

**Serveur :**
- [ ] UptimeRobot (uptime)
- [ ] Cloudflare Analytics (trafic)
- [ ] Wasabi Dashboard (stockage)

**Alertes à configurer :**
- [ ] Downtime > 2 minutes
- [ ] CPU > 80%
- [ ] RAM > 90%
- [ ] Disk > 80%
- [ ] Error rate > 5%

---

## 🔒 Sécurité Production

### ✅ Checklist Sécurité

**Application :**
- [ ] `APP_DEBUG=false`
- [ ] Clés API en variables d'environnement
- [ ] CSRF activé
- [ ] Rate limiting configuré
- [ ] Headers de sécurité (HSTS, CSP)

**Serveur :**
- [ ] Firewall activé (UFW)
- [ ] SSH key authentication
- [ ] Fail2ban installé
- [ ] SSL/TLS avec Let's Encrypt
- [ ] Automatic security updates

**Base de données :**
- [ ] Utilisateur dédié (pas root)
- [ ] Accès localhost uniquement
- [ ] Backup automatique quotidien
- [ ] Chiffrement des backups

---

## 📈 Optimisations Production

### Performance

**Laravel :**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer install --optimize-autoloader --no-dev
```

**Assets :**
```bash
npm run build  # Minification automatique
```

**Cache :**
- [ ] Redis pour sessions
- [ ] Redis pour cache
- [ ] OPcache PHP activé

### Scalabilité

**Horizontal :**
- [ ] Load balancer (Nginx/HAProxy)
- [ ] Multiple app servers
- [ ] Redis cluster
- [ ] Database replication

**Vertical :**
- [ ] Upgrade serveur (CPU/RAM)
- [ ] SSD storage
- [ ] Optimize queries

---

## 💰 Coûts Estimés

### Développement Local
**Total : 0€/mois** ✅

### Production Minimale
- VPS 2 CPU / 4 GB RAM : 10-20€/mois
- Domaine : 10€/an
- SSL (Let's Encrypt) : Gratuit
- **Total : ~15€/mois**

### Production avec CDN
- VPS : 15€/mois
- Wasabi (1 TB) : 7€/mois
- Domaine : 10€/an
- **Total : ~22€/mois**

### Production Scale
- VPS/Cloud : 50€/mois
- Wasabi : 15€/mois
- Cloudflare Pro : 20€/mois
- Monitoring : 10€/mois
- **Total : ~95€/mois**

---

## 🎯 Timeline Recommandée

### Semaine 1-2 : Développement Local ✅ (Actuel)
- ✅ Mise en place du code
- ✅ Tests locaux
- ✅ Configuration de base
- ⏳ Intégration API sociales

### Semaine 3-4 : MVP Testing
- Tests complets en local
- Configuration Stripe test
- Validation UX/UI
- Corrections bugs

### Semaine 5 : Préparation Production
- Choix hébergement
- Configuration domaine
- Setup serveur
- Backups

### Semaine 6 : Déploiement
- Migration base de données
- Déploiement code
- Tests production
- Activation Stripe live

### Semaine 7+ : Optimisation
- Migration Wasabi (si besoin)
- Optimisations performance
- Monitoring
- Scaling

---

## 📞 Support Migration

### Documentation Disponible

- ✅ `README.md` - Vue d'ensemble
- ✅ `INSTALLATION.md` - Installation locale
- ✅ `QUICKSTART.md` - Démarrage rapide
- ✅ `DOCUMENTATION.md` - Doc technique complète
- ✅ `MEDIA_STORAGE_GUIDE.md` - Stockage des médias
- ✅ `WASABI_SETUP.md` - Configuration Wasabi
- ✅ `DEPLOYMENT_CHECKLIST.md` - Ce fichier

### Aide Disponible

**Pour le développement local :**
- Lire `QUICKSTART.md`
- Exécuter `php artisan tenant:create`

**Pour la production :**
- Lire `DOCUMENTATION.md` section Déploiement
- Suivre cette checklist étape par étape

**Pour Wasabi (futur) :**
- Lire `WASABI_SETUP.md`
- Exécuter `php artisan media:migrate-to-wasabi`

---

## ✅ Résumé

### ✨ Situation Actuelle (Parfaite pour démarrer)

```
✅ Architecture complète
✅ Stockage local configuré
✅ Prêt pour le développement
✅ Prêt pour les tests
✅ Migration future facilitée
```

### 🚀 Quand Vous Serez Prêt

```
1. Suivre cette checklist
2. Configurer le serveur production
3. Déployer l'application
4. (Optionnel) Migrer vers Wasabi
5. Profiter ! 🎉
```

**Temps total estimé : 1-2 jours pour mise en production**

---

**🎊 Vous êtes prêt à développer localement, tout est configuré pour une migration facile vers la production quand le moment viendra !**

