# 🔧 Guide .htaccess - HashMyTag v1.2.1

## 📋 **Fichiers .htaccess Créés**

### **2 fichiers configurés pour Apache**

```
✅ .htaccess (racine)
   → Redirige vers /public

✅ public/.htaccess (principal)
   → URLs propres Laravel
   → Sécurité renforcée
   → Cache & compression
```

---

## 📂 **STRUCTURE**

```
hashmytag/
├── .htaccess                    ← Racine (redirige vers /public)
├── public/
│   ├── .htaccess               ← Principal (Laravel routing)
│   ├── index.php
│   └── widget.js
├── app/
├── config/
└── ...
```

---

## ⚙️ **CONFIGURATION INCLUSE**

### **1. Routing Laravel** ✅

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [L]
```

**Résultat** :
- ✅ URLs propres : `/dashboard` au lieu de `/index.php?page=dashboard`
- ✅ Routes Laravel fonctionnelles
- ✅ API endpoints accessibles

---

### **2. Sécurité** 🔒

```apache
# Protection XSS
Header set X-XSS-Protection "1; mode=block"
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"

# Bloquer fichiers sensibles
<FilesMatch "(\.env|composer\.json|package\.json)$">
    Deny from all
</FilesMatch>
```

**Résultat** :
- ✅ Protection contre XSS
- ✅ Empêche lecture .env, composer.json
- ✅ Headers sécurité configurés

---

### **3. HTTPS (optionnel)** 🔐

**Activé par défaut** : ❌ Commenté (pour dev local)

**Pour activer en production** :

```apache
# Décommenter ces lignes dans public/.htaccess :
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

**Résultat** :
- ✅ Redirection automatique HTTP → HTTPS
- ✅ SSL forcé

---

### **4. Compression GZIP** ⚡

```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/javascript
    # ... etc
</IfModule>
```

**Résultat** :
- ✅ HTML/CSS/JS compressés
- ✅ Réduction ~70% taille fichiers
- ✅ Chargement plus rapide

---

### **5. Cache Navigateur** 🚀

```apache
<IfModule mod_expires.c>
    # Images : 1 an
    ExpiresByType image/jpg "access plus 1 year"
    
    # CSS/JS : 1 mois
    ExpiresByType text/css "access plus 1 month"
    
    # HTML : 0 (pas de cache)
    ExpiresByType text/html "access plus 0 seconds"
</IfModule>
```

**Résultat** :
- ✅ Images cachées 1 an
- ✅ CSS/JS cachés 1 mois
- ✅ HTML toujours frais
- ✅ Moins de requêtes serveur

---

## 🧪 **TESTER LA CONFIGURATION**

### **Test 1 : Routing Laravel**

```bash
# Démarrer serveur
php artisan serve

# Tester dans navigateur
http://localhost:8000/dashboard
# ✅ Devrait afficher dashboard (pas 404)

http://localhost:8000/api/widget/posts?tenant=test
# ✅ Devrait retourner JSON
```

---

### **Test 2 : Fichiers sensibles bloqués**

```bash
# Tenter d'accéder à .env
http://localhost:8000/.env
# ✅ Devrait afficher 403 Forbidden

http://localhost:8000/composer.json
# ✅ Devrait afficher 403 Forbidden
```

---

### **Test 3 : Compression activée**

```bash
# Vérifier headers
curl -I http://localhost:8000 | grep -i "content-encoding"
# ✅ Devrait afficher : Content-Encoding: gzip
```

---

## 🌐 **CONFIGURATION APACHE REQUISE**

### **Modules Apache à activer** :

```bash
# Linux
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod expires
sudo a2enmod deflate
sudo systemctl restart apache2

# Vérifier
apache2 -M | grep -E "rewrite|headers|expires|deflate"
```

**Résultat attendu** :
```
rewrite_module (shared)
headers_module (shared)
expires_module (shared)
deflate_module (shared)
```

---

### **Configuration VirtualHost** (Production)

```apache
<VirtualHost *:80>
    ServerName hashmytag.com
    ServerAlias www.hashmytag.com
    
    DocumentRoot /var/www/hashmytag/public
    
    <Directory /var/www/hashmytag/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/hashmytag-error.log
    CustomLog ${APACHE_LOG_DIR}/hashmytag-access.log combined
</VirtualHost>
```

**Important** : `AllowOverride All` pour que .htaccess fonctionne !

---

## 🐛 **DÉPANNAGE**

### **Problème 1 : "404 Not Found" sur toutes les routes**

**Cause** : mod_rewrite pas activé

**Solution** :
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

---

### **Problème 2 : ".htaccess not working"**

**Cause** : `AllowOverride` désactivé

**Solution** :
```apache
# Dans /etc/apache2/sites-available/hashmytag.conf
<Directory /var/www/hashmytag/public>
    AllowOverride All  # ← Changer "None" en "All"
</Directory>
```

---

### **Problème 3 : "500 Internal Server Error"**

**Cause** : Syntaxe .htaccess invalide

**Solution** :
```bash
# Vérifier logs Apache
sudo tail -f /var/log/apache2/error.log

# Tester config Apache
apachectl configtest
```

---

### **Problème 4 : Compression pas activée**

**Cause** : mod_deflate pas activé

**Solution** :
```bash
sudo a2enmod deflate
sudo systemctl restart apache2
```

---

## 📊 **PERFORMANCE ATTENDUE**

### **Avec .htaccess optimisé** :

```
Avant :
  Page HTML : 250 KB
  CSS : 80 KB
  JS : 120 KB
  Total : 450 KB

Après (avec compression) :
  Page HTML : 75 KB (-70%)
  CSS : 24 KB (-70%)
  JS : 36 KB (-70%)
  Total : 135 KB (-70%)

Temps chargement :
  Avant : 1.5 s
  Après : 0.5 s (-66%)
```

---

## ✅ **CHECKLIST PRODUCTION**

```
☐ .htaccess créés (racine + public)
☐ mod_rewrite activé
☐ mod_headers activé
☐ mod_expires activé
☐ mod_deflate activé
☐ AllowOverride All dans VirtualHost
☐ DocumentRoot pointe vers /public
☐ HTTPS activé (décommenter redirections)
☐ Permissions correctes (755 dossiers, 644 fichiers)
☐ Tests effectués (routing, sécurité, compression)
```

---

## 🚀 **ALTERNATIVE : NGINX**

Si tu utilises **Nginx** au lieu d'Apache :

```nginx
# /etc/nginx/sites-available/hashmytag
server {
    listen 80;
    server_name hashmytag.com;
    root /var/www/hashmytag/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
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
    
    # Compression
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;
    
    # Cache
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

---

## 📖 **DOCUMENTATION ASSOCIÉE**

- `GUIDE_INSTALLATION_COMPLET.md` - Installation complète
- `DEPLOYMENT_CHECKLIST.md` - Checklist déploiement production
- `SCALABILITY_ANALYSIS.md` - Performance & scalabilité

---

**Document** : HTACCESS_GUIDE.md  
**Version** : 1.0  
**Date** : 15 Octobre 2025  
**Application** : HashMyTag v1.2.1

---

**✅ Fichiers .htaccess créés et configurés pour Apache !** 🚀

**Prochaine étape** : Tester l'application avec `php artisan serve`

