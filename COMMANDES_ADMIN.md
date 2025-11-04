# 🛠️ Commandes Utiles - Espace Admin

## Commandes Laravel pour l'Espace Admin

### Gestion de la Base de Données

```bash
# Créer la table admins (si pas encore fait)
php artisan migrate

# Créer les admins par défaut
php artisan db:seed --class=AdminSeeder

# Recréer toute la base avec les admins
php artisan migrate:fresh --seed

# Voir le statut des migrations
php artisan migrate:status
```

### Gestion des Routes

```bash
# Voir toutes les routes
php artisan route:list

# Voir seulement les routes admin
php artisan route:list --path=admin

# Voir les routes avec leur middleware
php artisan route:list --path=admin --columns=uri,method,middleware
```

### Créer des Admins Manuellement

#### Via Tinker (Console Interactive)

```bash
php artisan tinker
```

Puis dans Tinker :

```php
// Créer un super admin
App\Models\Admin::create([
    'name' => 'Mon Super Admin',
    'email' => 'superadmin@example.com',
    'password' => Hash::make('mon_mot_de_passe'),
    'role' => 'super_admin',
    'is_active' => true
]);

// Créer un admin standard
App\Models\Admin::create([
    'name' => 'Mon Admin',
    'email' => 'admin@example.com',
    'password' => Hash::make('password'),
    'role' => 'admin',
    'is_active' => true
]);

// Lister tous les admins
App\Models\Admin::all();

// Trouver un admin par email
App\Models\Admin::where('email', 'admin@kanban.com')->first();

// Changer le mot de passe d'un admin
$admin = App\Models\Admin::find(1);
$admin->password = Hash::make('nouveau_mot_de_passe');
$admin->save();

// Activer/Désactiver un admin
$admin = App\Models\Admin::find(2);
$admin->is_active = false;
$admin->save();

// Supprimer un admin
App\Models\Admin::find(3)->delete();
```

### Gestion des Tokens

```bash
php artisan tinker
```

```php
// Voir tous les tokens d'un admin
$admin = App\Models\Admin::find(1);
$admin->tokens;

// Supprimer tous les tokens d'un admin (forcer déconnexion)
$admin = App\Models\Admin::find(1);
$admin->tokens()->delete();

// Supprimer un token spécifique
\Laravel\Sanctum\PersonalAccessToken::findToken('1|xxxxx')->delete();
```

### Nettoyer le Cache

```bash
# Nettoyer tous les caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Ou tout en une commande
php artisan optimize:clear
```

### Logs et Debugging

```bash
# Voir les logs en temps réel
php artisan tail

# Vider les logs
echo "" > storage/logs/laravel.log
# Ou sur Windows
type nul > storage\logs\laravel.log
```

### Tester l'API Admin

#### PowerShell (Windows)

```powershell
# Connexion et récupération du token
$loginData = @{
    email = 'admin@kanban.com'
    password = 'password123'
} | ConvertTo-Json

$response = Invoke-RestMethod -Uri 'http://localhost:8000/api/admin/login' -Method Post -Body $loginData -ContentType 'application/json'

$token = $response.token
Write-Host "Token récupéré: $token" -ForegroundColor Green

# Dashboard
$headers = @{ Authorization = "Bearer $token" }
Invoke-RestMethod -Uri 'http://localhost:8000/api/admin/dashboard/stats' -Headers $headers

# Produits
Invoke-RestMethod -Uri 'http://localhost:8000/api/admin/products' -Headers $headers

# Produits en stock bas
Invoke-RestMethod -Uri 'http://localhost:8000/api/admin/products/low-stock' -Headers $headers
```

#### Bash/Linux/Mac

```bash
# Connexion
TOKEN=$(curl -s -X POST http://localhost:8000/api/admin/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@kanban.com","password":"password123"}' \
  | jq -r '.token')

echo "Token: $TOKEN"

# Dashboard
curl -X GET http://localhost:8000/api/admin/dashboard/stats \
  -H "Authorization: Bearer $TOKEN"

# Produits
curl -X GET http://localhost:8000/api/admin/products \
  -H "Authorization: Bearer $TOKEN"
```

## Commandes de Maintenance

### Réinitialiser un Mot de Passe Admin

```bash
php artisan tinker
```

```php
$admin = App\Models\Admin::where('email', 'admin@kanban.com')->first();
$admin->password = Hash::make('nouveau_mot_de_passe');
$admin->save();
echo "Mot de passe changé avec succès!";
```

### Créer un Nouveau Seeder

```bash
# Créer un seeder pour d'autres admins
php artisan make:seeder AdditionalAdminsSeeder

# Éditer le fichier database/seeders/AdditionalAdminsSeeder.php
# Puis exécuter
php artisan db:seed --class=AdditionalAdminsSeeder
```

### Créer un Nouveau Controller Admin

```bash
# Créer un controller dans le namespace Admin
php artisan make:controller Admin/NomController
```

### Créer un Middleware

```bash
php artisan make:middleware NomMiddleware
```

### Créer une Migration

```bash
php artisan make:migration add_field_to_admins_table --table=admins
```

## Scripts Utiles

### Script PowerShell : Créer un Admin

Créer un fichier `create-admin.ps1` :

```powershell
param(
    [Parameter(Mandatory=$true)]
    [string]$Name,
    
    [Parameter(Mandatory=$true)]
    [string]$Email,
    
    [Parameter(Mandatory=$true)]
    [string]$Password,
    
    [string]$Role = "admin"
)

$phpScript = @"
\$admin = App\Models\Admin::create([
    'name' => '$Name',
    'email' => '$Email',
    'password' => Hash::make('$Password'),
    'role' => '$Role',
    'is_active' => true
]);
echo 'Admin créé avec succès: ' . \$admin->email;
"@

php artisan tinker --execute="$phpScript"
```

Utilisation :
```powershell
.\create-admin.ps1 -Name "John Doe" -Email "john@example.com" -Password "password123" -Role "admin"
```

### Script PowerShell : Tester l'API

Créer un fichier `test-admin-api.ps1` :

```powershell
# Configuration
$baseUrl = "http://localhost:8000/api"
$email = "admin@kanban.com"
$password = "password123"

Write-Host "=== Test API Admin ===" -ForegroundColor Cyan

# 1. Connexion
Write-Host "`n1. Connexion..." -ForegroundColor Yellow
$loginData = @{
    email = $email
    password = $password
} | ConvertTo-Json

try {
    $response = Invoke-RestMethod -Uri "$baseUrl/admin/login" -Method Post -Body $loginData -ContentType 'application/json'
    $token = $response.token
    Write-Host "✓ Connexion réussie" -ForegroundColor Green
    Write-Host "Token: $token" -ForegroundColor Gray
} catch {
    Write-Host "✗ Échec de connexion" -ForegroundColor Red
    exit 1
}

$headers = @{
    Authorization = "Bearer $token"
}

# 2. Dashboard
Write-Host "`n2. Dashboard Stats..." -ForegroundColor Yellow
try {
    $stats = Invoke-RestMethod -Uri "$baseUrl/admin/dashboard/stats" -Headers $headers
    Write-Host "✓ Dashboard OK - $($stats.overview.total_products) produits" -ForegroundColor Green
} catch {
    Write-Host "✗ Échec dashboard" -ForegroundColor Red
}

# 3. Produits
Write-Host "`n3. Liste des produits..." -ForegroundColor Yellow
try {
    $products = Invoke-RestMethod -Uri "$baseUrl/admin/products" -Headers $headers
    Write-Host "✓ Produits OK - $($products.data.Count) produits trouvés" -ForegroundColor Green
} catch {
    Write-Host "✗ Échec produits" -ForegroundColor Red
}

# 4. Stock bas
Write-Host "`n4. Produits en stock bas..." -ForegroundColor Yellow
try {
    $lowStock = Invoke-RestMethod -Uri "$baseUrl/admin/products/low-stock" -Headers $headers
    Write-Host "✓ Stock bas OK - $($lowStock.Count) produits" -ForegroundColor Green
} catch {
    Write-Host "✗ Échec stock bas" -ForegroundColor Red
}

Write-Host "`n=== Tests terminés ===" -ForegroundColor Cyan
```

Utilisation :
```powershell
.\test-admin-api.ps1
```

## Commandes SQL Directes

```sql
-- Voir tous les admins
SELECT * FROM admins;

-- Créer un admin (hasher le password avant!)
INSERT INTO admins (name, email, password, role, is_active, created_at, updated_at)
VALUES ('New Admin', 'new@admin.com', '$2y$10$...', 'admin', 1, NOW(), NOW());

-- Activer un admin
UPDATE admins SET is_active = 1 WHERE email = 'admin@kanban.com';

-- Changer le rôle
UPDATE admins SET role = 'super_admin' WHERE email = 'user@kanban.com';

-- Supprimer un admin
DELETE FROM admins WHERE id = 3;

-- Compter les admins par rôle
SELECT role, COUNT(*) as count FROM admins GROUP BY role;

-- Voir les dernières connexions
SELECT name, email, last_login_at FROM admins ORDER BY last_login_at DESC;
```

## Dépannage

### Problème : Token invalide

```bash
# Nettoyer tous les tokens
php artisan tinker
App\Models\Admin::find(1)->tokens()->delete();

# Puis se reconnecter
```

### Problème : Admin désactivé

```bash
php artisan tinker
$admin = App\Models\Admin::where('email', 'admin@kanban.com')->first();
$admin->is_active = true;
$admin->save();
```

### Problème : Mot de passe oublié

```bash
php artisan tinker
$admin = App\Models\Admin::where('email', 'admin@kanban.com')->first();
$admin->password = Hash::make('password123');
$admin->save();
echo "Nouveau mot de passe: password123";
```

### Problème : Routes non trouvées

```bash
# Nettoyer et recréer le cache
php artisan route:clear
php artisan cache:clear
php artisan config:clear
```

## Monitoring

### Voir les admins connectés (avec tokens actifs)

```bash
php artisan tinker
```

```php
use Laravel\Sanctum\PersonalAccessToken;

$activeTokens = PersonalAccessToken::with('tokenable')
    ->where('tokenable_type', 'App\\Models\\Admin')
    ->get();

foreach ($activeTokens as $token) {
    echo $token->tokenable->name . " - " . $token->created_at . "\n";
}
```

### Statistiques Admin

```bash
php artisan tinker
```

```php
// Nombre total d'admins
App\Models\Admin::count();

// Admins actifs
App\Models\Admin::where('is_active', true)->count();

// Super admins
App\Models\Admin::where('role', 'super_admin')->count();

// Dernière connexion
App\Models\Admin::orderBy('last_login_at', 'desc')->first();
```

---

**Ces commandes vous aideront à gérer efficacement votre espace admin ! 🛠️**











