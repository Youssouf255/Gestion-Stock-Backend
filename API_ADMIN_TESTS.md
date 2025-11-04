# 🧪 Tests API Admin - Collection Postman/cURL

## Tests Rapides pour l'Espace Admin

### 1. Connexion Admin

```bash
curl -X POST http://localhost:8000/api/admin/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@kanban.com",
    "password": "password123"
  }'
```

**Résultat attendu:**
```json
{
  "message": "Connexion réussie",
  "admin": {
    "id": 1,
    "name": "Super Admin",
    "email": "admin@kanban.com",
    "role": "super_admin",
    "is_active": true
  },
  "token": "1|xxxxxxxxxxxxxxx"
}
```

> **Important:** Copiez le token retourné et utilisez-le dans les requêtes suivantes en remplaçant `{TOKEN}`

---

### 2. Dashboard Stats

```bash
curl -X GET http://localhost:8000/api/admin/dashboard/stats \
  -H "Authorization: Bearer {TOKEN}"
```

---

### 3. Profil Admin

```bash
curl -X GET http://localhost:8000/api/admin/me \
  -H "Authorization: Bearer {TOKEN}"
```

---

### 4. Liste des Produits

```bash
# Tous les produits
curl -X GET http://localhost:8000/api/admin/products \
  -H "Authorization: Bearer {TOKEN}"

# Avec filtres
curl -X GET "http://localhost:8000/api/admin/products?search=tomat&stock_status=low&per_page=10" \
  -H "Authorization: Bearer {TOKEN}"
```

---

### 5. Produits en Stock Bas

```bash
curl -X GET http://localhost:8000/api/admin/products/low-stock \
  -H "Authorization: Bearer {TOKEN}"
```

---

### 6. Créer un Produit

```bash
curl -X POST http://localhost:8000/api/admin/products \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Product",
    "product_id": "TEST-001",
    "category_id": 1,
    "supplier_id": 1,
    "buying_price": 5.00,
    "selling_price": 10.00,
    "quantity": 50,
    "unit": "pièce",
    "threshold_value": 10,
    "expiry_date": "2025-12-31"
  }'
```

---

### 7. Mettre à Jour un Produit

```bash
curl -X PUT http://localhost:8000/api/admin/products/1 \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "remaining_stock": 100
  }'
```

---

### 8. Mise à Jour en Masse du Stock

```bash
curl -X POST http://localhost:8000/api/admin/products/bulk-update-stock \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "products": [
      {"id": 1, "remaining_stock": 80},
      {"id": 2, "remaining_stock": 60}
    ]
  }'
```

---

### 9. Export CSV des Produits

```bash
curl -X GET http://localhost:8000/api/admin/products/export \
  -H "Authorization: Bearer {TOKEN}" \
  -o products_export.csv
```

---

### 10. Liste des Admins (Super Admin)

```bash
curl -X GET http://localhost:8000/api/admin/admins \
  -H "Authorization: Bearer {TOKEN}"
```

---

### 11. Créer un Nouvel Admin (Super Admin)

```bash
curl -X POST http://localhost:8000/api/admin/admins \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Admin",
    "email": "testadmin@kanban.com",
    "password": "password123",
    "role": "admin"
  }'
```

---

### 12. Changer le Mot de Passe

```bash
curl -X POST http://localhost:8000/api/admin/change-password \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "current_password": "password123",
    "new_password": "newpassword123",
    "new_password_confirmation": "newpassword123"
  }'
```

---

### 13. Déconnexion

```bash
curl -X POST http://localhost:8000/api/admin/logout \
  -H "Authorization: Bearer {TOKEN}"
```

---

## PowerShell (Windows)

### Connexion et Récupération du Token

```powershell
# Connexion
$body = @{
    email = 'admin@kanban.com'
    password = 'password123'
} | ConvertTo-Json

$response = Invoke-RestMethod -Uri 'http://localhost:8000/api/admin/login' -Method Post -Body $body -ContentType 'application/json'

# Afficher le token
$token = $response.token
Write-Host "Token: $token"

# Dashboard
$headers = @{
    Authorization = "Bearer $token"
}
Invoke-RestMethod -Uri 'http://localhost:8000/api/admin/dashboard/stats' -Headers $headers
```

---

## Collection Postman

### Configuration de Base

1. **Créer une collection** : "KANBAN Admin API"
2. **Créer une variable** : `base_url` = `http://localhost:8000`
3. **Créer une variable** : `admin_token` = (sera auto-remplie)

### Requêtes

#### 1. Login Admin
- **Méthode:** POST
- **URL:** `{{base_url}}/api/admin/login`
- **Body (JSON):**
```json
{
  "email": "admin@kanban.com",
  "password": "password123"
}
```
- **Test Script:**
```javascript
pm.test("Login successful", function () {
    pm.response.to.have.status(200);
    var jsonData = pm.response.json();
    pm.environment.set("admin_token", jsonData.token);
});
```

#### 2. Dashboard Stats
- **Méthode:** GET
- **URL:** `{{base_url}}/api/admin/dashboard/stats`
- **Headers:** `Authorization: Bearer {{admin_token}}`

#### 3. Get Products
- **Méthode:** GET
- **URL:** `{{base_url}}/api/admin/products`
- **Headers:** `Authorization: Bearer {{admin_token}}`

#### 4. Create Product
- **Méthode:** POST
- **URL:** `{{base_url}}/api/admin/products`
- **Headers:** `Authorization: Bearer {{admin_token}}`
- **Body (JSON):**
```json
{
  "name": "{{$randomProductName}}",
  "product_id": "PROD-{{$randomInt}}",
  "category_id": 1,
  "supplier_id": 1,
  "buying_price": 10.00,
  "selling_price": 15.00,
  "quantity": 100,
  "unit": "pièce",
  "threshold_value": 10
}
```

---

## Tests Automatisés (PHPUnit)

Pour créer des tests automatisés :

```php
// tests/Feature/AdminAuthTest.php
public function test_admin_can_login()
{
    $admin = Admin::factory()->create([
        'email' => 'test@admin.com',
        'password' => Hash::make('password'),
    ]);

    $response = $this->postJson('/api/admin/login', [
        'email' => 'test@admin.com',
        'password' => 'password',
    ]);

    $response->assertStatus(200)
             ->assertJsonStructure(['token', 'admin']);
}
```

---

## Vérifications de Sécurité

### 1. Accès Non Autorisé

```bash
# Sans token - devrait échouer
curl -X GET http://localhost:8000/api/admin/dashboard/stats
# Résultat: 401 Unauthorized
```

### 2. Token Invalide

```bash
# Avec faux token - devrait échouer
curl -X GET http://localhost:8000/api/admin/dashboard/stats \
  -H "Authorization: Bearer fake_token_123"
# Résultat: 401 Unauthorized
```

### 3. Admin vs Super Admin

```bash
# Connexion avec admin standard
curl -X POST http://localhost:8000/api/admin/login \
  -H "Content-Type: application/json" \
  -d '{"email": "user@kanban.com", "password": "password123"}'

# Essayer d'accéder à la gestion des admins - devrait échouer
curl -X GET http://localhost:8000/api/admin/admins \
  -H "Authorization: Bearer {ADMIN_TOKEN}"
# Résultat: 403 Forbidden
```

---

## Résultats Attendus

| Test | Status Code | Message |
|------|-------------|---------|
| Login valide | 200 | Connexion réussie |
| Login invalide | 422 | Les identifiants fournis sont incorrects |
| Dashboard avec token | 200 | Données retournées |
| Dashboard sans token | 401 | Unauthenticated |
| Admin accède gestion admins | 403 | Accès refusé |
| Super Admin accède gestion admins | 200 | Liste des admins |
| Créer produit | 201 | Produit créé |
| Export CSV | 200 | Fichier CSV téléchargé |

---

## Logs

Pour voir les logs en temps réel :

```bash
# Windows PowerShell
Get-Content backend/storage/logs/laravel.log -Wait -Tail 50

# Ou
php artisan tail
```

---

**Bons tests ! 🧪**











