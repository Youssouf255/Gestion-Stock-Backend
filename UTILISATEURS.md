# Guide de Gestion des Utilisateurs

Ce guide explique comment créer et gérer les utilisateurs de l'application KANBAN Stock Management.

## 📋 Utilisateurs Créés par Défaut

Lors de l'exécution du seeder, les utilisateurs suivants sont créés :

### Utilisateurs de Test

1. **Utilisateur Test**
   - Email: `user@kanban.com`
   - Mot de passe: `password123`

2. **John Doe**
   - Email: `john.doe@kanban.com`
   - Mot de passe: `password123`

3. **Jane Smith**
   - Email: `jane.smith@kanban.com`
   - Mot de passe: `password123`

## 🚀 Création d'Utilisateurs

### Méthode 1 : Via Seeder (Recommandé pour les utilisateurs de test)

1. Modifiez le fichier `database/seeders/UserSeeder.php` pour ajouter de nouveaux utilisateurs :

```php
$users = [
    [
        'name' => 'Votre Nom',
        'email' => 'votre.email@kanban.com',
        'password' => Hash::make('votre_mot_de_passe'),
        'email_verified_at' => now(),
    ],
    // Ajoutez plus d'utilisateurs ici...
];
```

2. Exécutez le seeder :
```bash
php artisan db:seed --class=UserSeeder
```

### Méthode 2 : Via Tinker (Console Laravel)

```bash
php artisan tinker
```

Puis dans Tinker :
```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

User::create([
    'name' => 'Nom Utilisateur',
    'email' => 'email@example.com',
    'password' => Hash::make('mot_de_passe'),
    'email_verified_at' => now(),
]);
```

### Méthode 3 : Via API (Inscription)

Vous pouvez créer un utilisateur via l'API d'inscription :

```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Nouvel Utilisateur",
    "email": "nouveau@example.com",
    "password": "mot_de_passe_securise",
    "password_confirmation": "mot_de_passe_securise"
  }'
```

### Méthode 4 : Via Base de Données Directement

Vous pouvez aussi insérer directement dans la base de données :

```sql
INSERT INTO users (name, email, password, email_verified_at, created_at, updated_at)
VALUES (
    'Nom Utilisateur',
    'email@example.com',
    '$2y$10$...', -- Hash bcrypt du mot de passe
    NOW(),
    NOW(),
    NOW()
);
```

**Note :** Pour générer un hash de mot de passe, utilisez :
```php
Hash::make('votre_mot_de_passe')
```

## 🔐 Authentification

### Login via API

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@kanban.com",
    "password": "password123"
  }'
```

Réponse :
```json
{
    "user": {
        "id": 1,
        "name": "Utilisateur Test",
        "email": "user@kanban.com",
        "email_verified_at": "2025-11-03T10:00:00.000000Z",
        "created_at": "2025-11-03T10:00:00.000000Z",
        "updated_at": "2025-11-03T10:00:00.000000Z"
    },
    "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
    "token_type": "Bearer"
}
```

### Utiliser le Token

Pour les requêtes authentifiées, incluez le token dans les headers :

```bash
curl -X GET http://localhost:8000/api/auth/me \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## 📝 Réinitialiser les Utilisateurs

Pour supprimer tous les utilisateurs et les recréer :

```bash
php artisan tinker
```

```php
use App\Models\User;
User::truncate(); // Supprime tous les utilisateurs
exit
```

Puis réexécutez le seeder :
```bash
php artisan db:seed --class=UserSeeder
```

## 🔒 Sécurité

- **Toujours** utilisez des mots de passe forts en production
- Changez les mots de passe par défaut après la première connexion
- Activez la vérification d'email en production
- Utilisez HTTPS en production

## 📚 Structure de la Table Users

- `id` : Identifiant unique
- `name` : Nom de l'utilisateur
- `email` : Adresse email (unique)
- `password` : Mot de passe hashé (bcrypt)
- `email_verified_at` : Date de vérification de l'email (nullable)
- `remember_token` : Token pour "Se souvenir de moi"
- `created_at` : Date de création
- `updated_at` : Date de mise à jour

## 🛠️ Commandes Utiles

```bash
# Créer un seul utilisateur via artisan
php artisan tinker
>>> User::create(['name' => 'Test', 'email' => 'test@test.com', 'password' => Hash::make('pass')]);

# Lister tous les utilisateurs
php artisan tinker
>>> User::all();

# Changer le mot de passe d'un utilisateur
php artisan tinker
>>> $user = User::where('email', 'user@kanban.com')->first();
>>> $user->password = Hash::make('nouveau_mot_de_passe');
>>> $user->save();
```




