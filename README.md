# Backend Laravel - KANBAN Stock Management

## 🚀 Installation et Configuration Complétée

Le backend Laravel a été créé et configuré avec succès !

### ✅ Ce qui a été fait

1. **Projet Laravel créé** (Laravel 8.x)
2. **Base de données configurée** : `kanban_stock`
3. **Migrations créées** pour toutes les tables :
   - `categories`
   - `suppliers`
   - `stores`
   - `products`
   - `orders`

4. **Modèles Eloquent créés** :
   - Category
   - Supplier
   - Store
   - Product
   - Order

5. **Controllers API créés** :
   - DashboardController
   - ProductController
   - CategoryController
   - SupplierController
   - StoreController
   - OrderController

6. **Routes API configurées** dans `routes/api.php`

7. **CORS configuré** pour Angular (http://localhost:4200)

8. **Seeders créés** avec données de test

9. **Base de données peuplée** avec des données de test

10. **Serveur démarré** sur http://localhost:8000

### 📊 Données de Test Disponibles

#### Catégories (8)
- Vegetable, Instant Food, Household, Beverages, Dairy, Snacks, Health, Personal Care

#### Fournisseurs (5)
- Richard Martin (Kit Kat)
- Tom Homan (Maaza)
- Veandir (Dairy Milk)
- Charin (Tomato)
- Hoffman (Milk Bikis)

#### Magasins (3)
- KANBAN Store - Centre-ville Branch (Paris)
- KANBAN Store - Nord Branch (Lyon)
- KANBAN Store - Sud Branch (Marseille)

#### Produits (7)
- Maggi (Instant Food)
- Bru Coffee (Beverages)
- Red Bull (Beverages)
- Tomato (Vegetable) - **Stock bas**
- Onion (Vegetable)
- Milk (Dairy)
- Chips Lays (Snacks) - **Stock bas**

#### Commandes (4)
- ORD-001, ORD-002, ORD-003, ORD-004

### 🔌 Endpoints API Disponibles

#### Dashboard
- `GET /api/dashboard/stats` - Statistiques du tableau de bord
- `GET /api/dashboard/best-selling-categories` - Meilleures catégories
- `GET /api/dashboard/best-selling-products` - Meilleurs produits
- `GET /api/dashboard/sales-chart` - Données graphique ventes

#### Products
- `GET /api/products` - Liste des produits (avec pagination, filtres)
- `POST /api/products` - Créer un produit
- `GET /api/products/{id}` - Détails d'un produit
- `PUT /api/products/{id}` - Mettre à jour un produit
- `DELETE /api/products/{id}` - Supprimer un produit
- `GET /api/products/low-stock` - Produits en stock bas

#### Categories
- `GET /api/categories` - Liste des catégories
- `POST /api/categories` - Créer une catégorie
- `GET /api/categories/{id}` - Détails d'une catégorie
- `PUT /api/categories/{id}` - Mettre à jour une catégorie
- `DELETE /api/categories/{id}` - Supprimer une catégorie

#### Suppliers
- `GET /api/suppliers` - Liste des fournisseurs
- `POST /api/suppliers` - Créer un fournisseur
- `GET /api/suppliers/{id}` - Détails d'un fournisseur
- `PUT /api/suppliers/{id}` - Mettre à jour un fournisseur
- `DELETE /api/suppliers/{id}` - Supprimer un fournisseur

#### Stores
- `GET /api/stores` - Liste des magasins
- `POST /api/stores` - Créer un magasin
- `GET /api/stores/{id}` - Détails d'un magasin
- `PUT /api/stores/{id}` - Mettre à jour un magasin
- `DELETE /api/stores/{id}` - Supprimer un magasin

#### Orders
- `GET /api/orders` - Liste des commandes
- `POST /api/orders` - Créer une commande
- `GET /api/orders/{id}` - Détails d'une commande
- `PUT /api/orders/{id}` - Mettre à jour une commande
- `DELETE /api/orders/{id}` - Supprimer une commande

### 🛠️ Commandes Utiles

```bash
# Démarrer le serveur Laravel
php artisan serve

# Voir les routes
php artisan route:list

# Réinitialiser la base de données
php artisan migrate:fresh --seed

# Créer une nouvelle migration
php artisan make:migration create_table_name

# Créer un nouveau controller
php artisan make:controller ControllerName

# Créer un nouveau modèle
php artisan make:model ModelName

# Créer un nouveau seeder
php artisan make:seeder SeederName

# Nettoyer le cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 🔧 Configuration

Le fichier `.env` est configuré pour :
- Base de données : `kanban_stock`
- Serveur : `http://localhost:8000`
- CORS : Autorisé pour `http://localhost:4200`

### 📝 Notes

- Le serveur Laravel tourne sur **http://localhost:8000**
- L'API est accessible sur **http://localhost:8000/api**
- Le frontend Angular utilise maintenant l'API Laravel (mode mock désactivé)
- CORS est configuré pour permettre les requêtes depuis Angular
- Les images peuvent être uploadées dans `storage/app/public`

## 🔐 Espace Admin

Un **espace administrateur complet** a été configuré pour gérer le système !

### Comptes Admin Disponibles

| Rôle | Email | Mot de passe | Accès |
|------|-------|--------------|-------|
| **Super Admin** | `admin@kanban.com` | `password123` | Accès complet |
| **Admin** | `user@kanban.com` | `password123` | Gestion produits |

### Fonctionnalités Admin

✅ **Authentification sécurisée** (Laravel Sanctum)  
✅ **Dashboard admin** avec statistiques avancées  
✅ **Gestion complète des produits**  
  - Filtres avancés  
  - Mise à jour en masse du stock  
  - Alertes stock bas / expiration  
  - Export CSV  
✅ **Gestion des administrateurs** (Super Admin)  
✅ **Protection par middlewares**  

### Routes Admin

```
POST   /api/admin/login                        # Connexion
GET    /api/admin/dashboard/stats              # Dashboard
GET    /api/admin/products                     # Gestion produits
POST   /api/admin/products/bulk-update-stock   # Mise à jour masse
GET    /api/admin/admins                       # Gestion admins (Super Admin)
```

📚 **Documentation complète** : Consultez `ADMIN_GUIDE.md` pour tous les détails

### Test Rapide

```bash
# Connexion
curl -X POST http://localhost:8000/api/admin/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@kanban.com","password":"password123"}'
```

---

### 🎯 Prochaines Étapes Possibles

1. ~~Ajouter l'authentification avec Laravel Sanctum~~ ✅ **Fait !**
2. Créer une interface Angular pour l'espace admin
3. Ajouter des tests unitaires et d'intégration
4. Implémenter la validation côté serveur plus approfondie
5. Ajouter des notifications et alertes email
6. Ajouter des logs d'activité pour l'admin
7. Implémenter l'authentification à deux facteurs (2FA)
8. Optimiser les requêtes avec eager loading
9. Ajouter la recherche avancée et les filtres complexes
10. Créer des rapports et analytics avancés

---

✨ **Le backend est prêt à être utilisé avec le frontend Angular !**  
🔐 **L'espace admin est opérationnel et sécurisé !**
