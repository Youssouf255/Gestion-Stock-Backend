# 🔐 Guide Espace Admin - KANBAN Stock Management

## ✅ Espace Admin Configuré avec Succès !

Un espace administrateur complet a été créé pour gérer le système KANBAN de gestion de stock.

## 👤 Comptes Administrateurs Créés

### Super Admin
- **Email**: `admin@kanban.com`
- **Mot de passe**: `password123`
- **Rôle**: Super Admin (accès complet)

### Admin Standard
- **Email**: `user@kanban.com`
- **Mot de passe**: `password123`
- **Rôle**: Admin (accès standard)

## 🔌 Endpoints API Admin

### Authentication

#### Connexion Admin
```http
POST /api/admin/login
Content-Type: application/json

{
  "email": "admin@kanban.com",
  "password": "password123"
}

Response:
{
  "message": "Connexion réussie",
  "admin": {...},
  "token": "1|xxxxxxxxxxxxxxxxxxxxxxxx"
}
```

#### Déconnexion
```http
POST /api/admin/logout
Authorization: Bearer {token}
```

#### Profil Admin
```http
GET /api/admin/me
Authorization: Bearer {token}
```

#### Changer le Mot de Passe
```http
POST /api/admin/change-password
Authorization: Bearer {token}

{
  "current_password": "password123",
  "new_password": "newpassword123",
  "new_password_confirmation": "newpassword123"
}
```

### Dashboard Admin

#### Statistiques Dashboard
```http
GET /api/admin/dashboard/stats
Authorization: Bearer {token}
```

Retourne:
- Vue d'ensemble (produits, catégories, fournisseurs, commandes, admins)
- Statistiques produits (total, stock bas, rupture, valeur totale)
- Statistiques commandes (total, en cours, livrés, valeur totale)
- Activités récentes

#### Logs d'Activité
```http
GET /api/admin/dashboard/activity-logs
Authorization: Bearer {token}
```

### Gestion des Produits

#### Liste des Produits (avec filtres avancés)
```http
GET /api/admin/products?search={query}&category_id={id}&supplier_id={id}&stock_status={low|out|in_stock}&sort_by={field}&sort_order={asc|desc}&per_page={number}
Authorization: Bearer {token}
```

#### Créer un Produit
```http
POST /api/admin/products
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
  "name": "Nouveau Produit",
  "product_id": "PROD-XXX",
  "category_id": 1,
  "supplier_id": 1,
  "buying_price": 10.00,
  "selling_price": 15.00,
  "quantity": 100,
  "unit": "pièce",
  "threshold_value": 10,
  "expiry_date": "2025-12-31",
  "image": [fichier]
}
```

#### Mettre à Jour un Produit
```http
PUT /api/admin/products/{id}
Authorization: Bearer {token}
```

#### Supprimer un Produit
```http
DELETE /api/admin/products/{id}
Authorization: Bearer {token}
```

#### Mise à Jour en Masse du Stock
```http
POST /api/admin/products/bulk-update-stock
Authorization: Bearer {token}

{
  "products": [
    { "id": 1, "remaining_stock": 50 },
    { "id": 2, "remaining_stock": 75 }
  ]
}
```

#### Produits en Stock Bas
```http
GET /api/admin/products/low-stock
Authorization: Bearer {token}
```

#### Produits en Rupture
```http
GET /api/admin/products/out-of-stock
Authorization: Bearer {token}
```

#### Produits Expirant Bientôt
```http
GET /api/admin/products/expiring-soon
Authorization: Bearer {token}
```

#### Exporter les Produits (CSV)
```http
GET /api/admin/products/export
Authorization: Bearer {token}
```

### Gestion des Admins (Super Admin Only)

#### Liste des Admins
```http
GET /api/admin/admins?search={query}&role={admin|super_admin}&is_active={true|false}
Authorization: Bearer {token}
```

#### Créer un Admin
```http
POST /api/admin/admins
Authorization: Bearer {token}

{
  "name": "Nouvel Admin",
  "email": "newadmin@kanban.com",
  "password": "password123",
  "role": "admin"
}
```

#### Mettre à Jour un Admin
```http
PUT /api/admin/admins/{id}
Authorization: Bearer {token}

{
  "name": "Nom Modifié",
  "email": "newemail@kanban.com",
  "role": "super_admin",
  "is_active": true
}
```

#### Supprimer un Admin
```http
DELETE /api/admin/admins/{id}
Authorization: Bearer {token}
```

#### Activer/Désactiver un Admin
```http
POST /api/admin/admins/{id}/toggle-status
Authorization: Bearer {token}
```

## 🛡️ Sécurité et Permissions

### Niveaux d'Accès

1. **Admin Standard** (`admin`)
   - ✅ Accès au dashboard
   - ✅ Gestion complète des produits
   - ✅ Vue des statistiques
   - ✅ Export des données
   - ❌ Gestion des autres admins

2. **Super Admin** (`super_admin`)
   - ✅ Tous les droits Admin
   - ✅ Gestion des administrateurs
   - ✅ Activation/Désactivation des comptes admin
   - ✅ Accès complet au système

### Middlewares

- `admin`: Vérifie que l'utilisateur est un admin actif
- `super_admin`: Vérifie que l'utilisateur est un super admin

### Token d'Authentification

- Utilise Laravel Sanctum pour l'authentification par token
- Le token est retourné lors de la connexion
- Doit être inclus dans le header `Authorization: Bearer {token}` pour toutes les requêtes protégées
- Les tokens n'expirent pas par défaut (configurable dans `config/sanctum.php`)

## 📊 Fonctionnalités Principales

### 1. Dashboard Admin
- Vue d'ensemble complète du système
- Statistiques en temps réel
- Alertes pour stocks bas
- Suivi des activités récentes

### 2. Gestion Avancée des Produits
- Recherche et filtres multiples
- Tri personnalisable
- Mise à jour en masse du stock
- Alertes automatiques (stock bas, expiration)
- Export CSV
- Gestion des images

### 3. Gestion des Administrateurs
- Création et gestion des comptes admin
- Gestion des rôles (Admin / Super Admin)
- Activation/Désactivation des comptes
- Changement de mot de passe sécurisé

### 4. Système d'Authentification
- Connexion sécurisée par token
- Protection des routes par middleware
- Gestion de session
- Déconnexion sécurisée

## 🔧 Configuration

### Modèle Admin
Fichier: `app/Models/Admin.php`
- Utilise Laravel Sanctum pour les tokens
- Champs: name, email, password, role, is_active, last_login_at

### Middlewares
- `app/Http/Middleware/AdminMiddleware.php`
- `app/Http/Middleware/SuperAdminMiddleware.php`

### Routes
Fichier: `routes/api.php`
- Routes publiques: `/api/admin/login`
- Routes protégées: `/api/admin/*` (requiert authentification)
- Routes super admin: `/api/admin/admins/*` (requiert rôle super_admin)

## 📝 Exemples d'Utilisation

### Connexion et Utilisation du Token

```bash
# 1. Connexion
curl -X POST http://localhost:8000/api/admin/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@kanban.com","password":"password123"}'

# Réponse:
# {
#   "message": "Connexion réussie",
#   "admin": {...},
#   "token": "1|xxxxx"
# }

# 2. Utiliser le token pour accéder aux routes protégées
curl -X GET http://localhost:8000/api/admin/dashboard/stats \
  -H "Authorization: Bearer 1|xxxxx"

# 3. Créer un produit
curl -X POST http://localhost:8000/api/admin/products \
  -H "Authorization: Bearer 1|xxxxx" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Nouveau Produit",
    "product_id": "PROD-999",
    "category_id": 1,
    "supplier_id": 1,
    "buying_price": 10.00,
    "selling_price": 15.00,
    "quantity": 100,
    "unit": "pièce",
    "threshold_value": 10
  }'
```

### Gestion du Stock

```bash
# Mise à jour en masse
curl -X POST http://localhost:8000/api/admin/products/bulk-update-stock \
  -H "Authorization: Bearer 1|xxxxx" \
  -H "Content-Type: application/json" \
  -d '{
    "products": [
      {"id": 1, "remaining_stock": 50},
      {"id": 2, "remaining_stock": 100}
    ]
  }'

# Produits en stock bas
curl -X GET http://localhost:8000/api/admin/products/low-stock \
  -H "Authorization: Bearer 1|xxxxx"

# Export CSV
curl -X GET http://localhost:8000/api/admin/products/export \
  -H "Authorization: Bearer 1|xxxxx" \
  -o products.csv
```

## 🔄 Prochaines Étapes

Pour étendre l'espace admin, vous pouvez :

1. **Ajouter des logs d'activité**
   - Créer une table `activity_logs`
   - Enregistrer toutes les actions admin

2. **Implémenter des notifications**
   - Alertes email pour stock bas
   - Notifications push pour les super admins

3. **Ajouter des rapports**
   - Rapports de ventes
   - Rapports de stock
   - Analyse des tendances

4. **Créer un frontend admin**
   - Interface Angular pour l'admin
   - Dashboard interactif
   - Gestion visuelle des produits

5. **Améliorer la sécurité**
   - Authentification à deux facteurs (2FA)
   - Limitation des tentatives de connexion
   - Logs de sécurité

## 🧪 Tests

Pour tester l'espace admin :

```bash
# Connexion
POST http://localhost:8000/api/admin/login
{
  "email": "admin@kanban.com",
  "password": "password123"
}

# Dashboard
GET http://localhost:8000/api/admin/dashboard/stats
Authorization: Bearer {token}

# Produits
GET http://localhost:8000/api/admin/products
Authorization: Bearer {token}
```

---

## 📞 Support

Pour toute question sur l'espace admin :
- Consultez les logs : `storage/logs/laravel.log`
- Vérifiez les routes : `php artisan route:list --path=admin`
- Testez avec Postman ou curl

**Bon développement ! 🚀**











