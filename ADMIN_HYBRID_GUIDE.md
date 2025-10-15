# 🔧 Guide Admin - Gestion Solution Hybride

## 🎯 Vue d'Ensemble

La solution hybride permet à vos clients de choisir entre :
- **Mode Simple** : API HashMyTag (centralisé)
- **Mode Avancé** : Connexion leur compte (+20€/mois)

---

## ⚙️ Configuration Initiale

### **1. Configuration Stripe**

Créez les produits add-ons dans votre Stripe Dashboard :

1. Aller sur [Stripe Dashboard](https://dashboard.stripe.com)
2. Produits → Créer un produit
3. Créer 3 produits :

**Produit 1 : Instagram Connection**
- Nom : Connexion compte Instagram (Mode Avancé)
- Prix : 20€ récurrent mensuel
- Copier le Price ID : `price_...`

**Produit 2 : Facebook Connection**
- Nom : Connexion compte Facebook (Mode Avancé)
- Prix : 20€ récurrent mensuel
- Copier le Price ID : `price_...`

**Produit 3 : Twitter Connection**
- Nom : Connexion compte Twitter (Mode Avancé)
- Prix : 20€ récurrent mensuel
- Copier le Price ID : `price_...`

4. Dans `.env`, ajouter :
```env
STRIPE_PRICE_INSTAGRAM_CONNECTION=price_...
STRIPE_PRICE_FACEBOOK_CONNECTION=price_...
STRIPE_PRICE_TWITTER_CONNECTION=price_...
```

---

### **2. Configuration OAuth**

Les credentials OAuth doivent être configurés dans `.env` :

```env
# Instagram (via Facebook)
INSTAGRAM_APP_ID=votre_app_id
INSTAGRAM_APP_SECRET=votre_app_secret

# Facebook
FACEBOOK_CLIENT_ID=votre_app_id
FACEBOOK_CLIENT_SECRET=votre_app_secret

# Twitter
TWITTER_CLIENT_ID=votre_client_id
TWITTER_CLIENT_SECRET=votre_client_secret

# Google (pour OAuth)
GOOGLE_CLIENT_ID=votre_client_id
GOOGLE_CLIENT_SECRET=votre_client_secret
```

**URLs de callback à configurer sur chaque plateforme :**
```
https://yourdomain.com/connect/instagram/callback
https://yourdomain.com/connect/facebook/callback
https://yourdomain.com/connect/twitter/callback
```

---

### **3. Migration Base de Données**

Pour les tenants existants, exécuter la migration add-ons :

```bash
# Pour chaque tenant
php artisan migrate --database=tenant --path=database/migrations/tenant
```

---

## 📊 Monitoring

### **Métriques à Surveiller**

**1. Adoption Mode Avancé**
```sql
-- Nombre de feeds en mode avancé
SELECT COUNT(*) FROM feeds WHERE credentials IS NOT NULL;

-- Par tenant
SELECT tenant_id, COUNT(*) as advanced_feeds 
FROM feeds 
WHERE credentials IS NOT NULL 
GROUP BY tenant_id;
```

**2. Revenus Add-ons**
```sql
-- Addons actifs
SELECT addon_key, COUNT(*) as count 
FROM tenant_addons 
WHERE active = 1 
GROUP BY addon_key;
```

**3. Tokens Expirés**
```bash
# Logs des refresh
tail -f storage/logs/laravel.log | grep "Token refresh"
```

---

## 🛠️ Commandes Admin

### **Refresh Tokens Manuellement**

```bash
# Tous les tenants
php artisan tokens:refresh

# Avec force (même si pas expiré)
php artisan tokens:refresh --force
```

### **Vérifier Status Token**

```bash
php artisan tinker
```

```php
$tenant = Tenant::find(1);
$tenant->switchDatabase();

$feeds = Feed::whereNotNull('credentials')->get();

foreach($feeds as $feed) {
    $expires = $feed->credentials['expires_at'] ?? 'N/A';
    echo "Feed {$feed->id}: {$feed->type} - Expires: {$expires}\n";
}
```

### **Activer Add-on Manuellement**

```bash
php artisan tinker
```

```php
$tenant = Tenant::find(1);
$tenant->activateAddon('instagram_connection');
```

---

## 📈 Analytics & Rapports

### **Rapport Mensuel**

Créer un rapport incluant :

1. **Adoption Mode Avancé**
   - Nombre de clients en mode simple
   - Nombre de clients en mode avancé
   - Taux de conversion

2. **Revenus Add-ons**
   - Revenue mensuel add-ons
   - Moyenne par client
   - Projection

3. **Performance Technique**
   - Taux d'erreur OAuth
   - Temps de synchronisation
   - Tokens expirés/renouvelés

---

## 🆘 Support Client

### **Issues Communes**

**1. "Comment activer le mode avancé ?"**
→ Guide : `GUIDE_MODE_AVANCE.md`
→ Vérifier plan (Business+ requis)
→ Vérifier achat add-on

**2. "Mon token a expiré"**
→ Check logs : `storage/logs/laravel.log`
→ Run : `php artisan tokens:refresh`
→ Demander au client de reconnecter

**3. "OAuth ne fonctionne pas"**
→ Vérifier callbacks configurés sur platforms
→ Vérifier credentials .env
→ Vérifier logs Laravel
→ Tester avec ngrok si local

**4. "Limite API atteinte"**
→ Proposer Mode Avancé
→ Upgrade vers plan supérieur
→ Optimiser fréquence sync

---

## 🔒 Sécurité

### **Bonnes Pratiques**

1. **Credentials Chiffrés**
   - Les tokens sont stockés en JSON (non chiffrés pour l'instant)
   - TODO: Ajouter chiffrement si données sensibles

2. **Validation Ownership**
   - Vérifier que le feed appartient au tenant avant OAuth
   - Implémenté dans `FeedConnectionController`

3. **Session Security**
   - Session utilisée pour stocker `connecting_feed_id`
   - Nettoyée après callback
   - Timeout automatique

4. **Rate Limiting**
   - Limiter les tentatives OAuth
   - TODO: Ajouter middleware rate limit sur routes /connect/*

---

## 📊 KPIs & Objectifs

### **Objectifs Business**

**Mois 1 :**
- 5% des clients en mode avancé
- +500€ revenue add-ons

**Mois 3 :**
- 10% des clients en mode avancé
- +1,500€ revenue add-ons

**Mois 6 :**
- 20% des clients en mode avancé
- +3,000€ revenue add-ons
- ROI atteint

### **Objectifs Techniques**

- Taux succès OAuth > 95%
- Temps sync < 500ms
- Tokens refresh auto > 90%
- Zero downtime

---

## 🔧 Maintenance

### **Tâches Quotidiennes**

```bash
# Automatique via scheduler
- Refresh tokens expirés
- Sync feeds
- Clean analytics
```

### **Tâches Hebdomadaires**

- Vérifier logs erreurs OAuth
- Monitorer utilisation API
- Check clients avec tokens expirés

### **Tâches Mensuelles**

- Rapport adoption mode avancé
- Analyse revenus add-ons
- Optimisations si nécessaire

---

## 🎯 Roadmap Améliorations

### **Court Terme (1-3 mois)**

- [ ] Email notifications token expiré
- [ ] Dashboard admin stats mode avancé
- [ ] Chiffrement credentials
- [ ] Rate limiting OAuth

### **Moyen Terme (3-6 mois)**

- [ ] Support TikTok
- [ ] Support LinkedIn
- [ ] Analytics avancées par mode
- [ ] A/B testing pricing

### **Long Terme (6-12 mois)**

- [ ] White label OAuth
- [ ] API publique pour devs
- [ ] Multi-account support
- [ ] Advanced permissions granulaires

---

## 📞 Contacts

**Questions techniques ?**
- Email : dev@hashmytag.com
- Slack : #tech-support

**Questions business ?**
- Email : sales@hashmytag.com
- Slack : #business

---

**🎊 Solution Hybride Active et Opérationnelle !**

