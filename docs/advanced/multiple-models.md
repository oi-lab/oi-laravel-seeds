---
title: Multiple Models
order: 2
---

# Multiple Models

A single seeder can handle multiple Eloquent models with separate JSON files for each model.

## Configuration

Use arrays for `$jsonFilename` and `$modelClass`:

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Database\Seeder;
use OiLab\OiLaravelSeeds\Traits\ExportableSeeder;

class UserWithProfileSeeder extends Seeder
{
    use ExportableSeeder;

    protected array $jsonFilename = ['users.json', 'profiles.json'];

    protected array $modelClass = [User::class, Profile::class];

    protected string $uniqueBy = 'id';

    public function run(): void
    {
        $this->importData();
    }
}
```

## Array Pairing

The arrays must have the same length and are paired by index:

```php
protected array $jsonFilename = [
    'users.json',          // index 0
    'profiles.json',       // index 1
    'settings.json',       // index 2
];

protected array $modelClass = [
    User::class,           // index 0
    Profile::class,        // index 1
    UserSetting::class,    // index 2
];
```

Each model is imported from its corresponding JSON file in order:

1. `User::class` ← `users.json`
2. `Profile::class` ← `profiles.json`
3. `UserSetting::class` ← `settings.json`

## Unique Key Configuration

The `$uniqueBy` property applies to all models:

```php
protected array $jsonFilename = ['users.json', 'profiles.json'];

protected array $modelClass = [User::class, Profile::class];

protected string $uniqueBy = 'id';
```

Both `User` and `Profile` records are matched by `id` when importing.

For composite keys, the same configuration applies to all models:

```php
protected array $uniqueBy = ['tenant_id', 'slug'];
```

Both users and profiles are matched by the combination of `tenant_id` and `slug`.

If different models need different unique keys, create separate seeders for each.

## Example: User and Profile

### JSON Files

**storage/app/private/seeders/users.json:**

```json
[
    {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "created_at": "2025-01-01T00:00:00Z",
        "updated_at": "2025-01-01T00:00:00Z"
    },
    {
        "id": 2,
        "name": "Jane Smith",
        "email": "jane@example.com",
        "created_at": "2025-01-01T00:00:00Z",
        "updated_at": "2025-01-01T00:00:00Z"
    }
]
```

**storage/app/private/seeders/profiles.json:**

```json
[
    {
        "id": 1,
        "user_id": 1,
        "bio": "Software engineer from San Francisco",
        "avatar_url": "https://example.com/avatars/1.jpg",
        "created_at": "2025-01-01T00:00:00Z",
        "updated_at": "2025-01-01T00:00:00Z"
    },
    {
        "id": 2,
        "user_id": 2,
        "bio": "Product designer from New York",
        "avatar_url": "https://example.com/avatars/2.jpg",
        "created_at": "2025-01-01T00:00:00Z",
        "updated_at": "2025-01-01T00:00:00Z"
    }
]
```

### Seeder Class

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Database\Seeder;
use OiLab\OiLaravelSeeds\Traits\ExportableSeeder;

class UserWithProfileSeeder extends Seeder
{
    use ExportableSeeder;

    protected array $jsonFilename = ['users.json', 'profiles.json'];

    protected array $modelClass = [User::class, Profile::class];

    protected string $uniqueBy = 'id';

    public function run(): void
    {
        $this->importData();
    }
}
```

### Execution

```bash
php artisan db:seed --class=UserWithProfileSeeder
```

Output:

```
Importing UserWithProfileSeeder...
Importing User from users.json
Created: 2, Updated: 0
Importing Profile from profiles.json
Created: 2, Updated: 0
UserWithProfileSeeder imported successfully.
```

## Exporting Multiple Models

When exporting a seeder with multiple models:

```bash
php artisan seed:export --seeder=UserWithProfileSeeder
```

Both JSON files are generated:

```
Exporting UserWithProfileSeeder...
Exported 2 users to storage/app/private/seeders/users.json
Exported 2 profiles to storage/app/private/seeders/profiles.json
```

### Export with Relations

```bash
php artisan seed:export --seeder=UserWithProfileSeeder --with-relations
```

Relations are included in the JSON for each model.

## Filtering Attributes for Multiple Models

Override `getExportableAttributes()` to filter attributes. The method receives the model instance, allowing per-model filtering:

```php
protected function getExportableAttributes(mixed $model): array
{
    if ($model instanceof User) {
        return [
            'id',
            'name',
            'email',
            'created_at',
            'updated_at',
        ];
    }

    if ($model instanceof Profile) {
        return [
            'id',
            'user_id',
            'bio',
            'avatar_url',
            'created_at',
            'updated_at',
        ];
    }

    return $model->getAttributes();
}
```

This allows different attributes to be exported for each model.

## Handling Different Unique Keys

If models need different unique keys, create separate seeders:

```php
// UserSeeder.php
protected string $jsonFilename = 'users.json';
protected string $modelClass = User::class;
protected string $uniqueBy = 'email'; // Users match by email

// ProfileSeeder.php
protected string $jsonFilename = 'profiles.json';
protected string $modelClass = Profile::class;
protected string $uniqueBy = 'id'; // Profiles match by id

protected array $dependencies = [UserSeeder::class];
```

Then import both:

```bash
php artisan seed:import
```

## Complex Example: Multi-Tenant Setup

```php
<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\TenantSetting;
use Illuminate\Database\Seeder;
use OiLab\OiLaravelSeeds\Traits\ExportableSeeder;

class TenantSeeder extends Seeder
{
    use ExportableSeeder;

    protected array $jsonFilename = [
        'tenants.json',
        'tenant_users.json',
        'tenant_settings.json',
    ];

    protected array $modelClass = [
        Tenant::class,
        TenantUser::class,
        TenantSetting::class,
    ];

    protected string $uniqueBy = 'id';

    public function run(): void
    {
        $this->importData();
    }

    protected function getExportableAttributes(mixed $model): array
    {
        return match (true) {
            $model instanceof Tenant => [
                'id',
                'slug',
                'name',
                'logo_url',
                'created_at',
                'updated_at',
            ],
            $model instanceof TenantUser => [
                'id',
                'tenant_id',
                'user_id',
                'role',
                'created_at',
                'updated_at',
            ],
            $model instanceof TenantSetting => [
                'id',
                'tenant_id',
                'key',
                'value',
                'created_at',
                'updated_at',
            ],
            default => $model->getAttributes(),
        };
    }
}
```

This seeder imports three related models from three separate JSON files.

## Best Practices

- **Keep related models together** — Group models that are logically related
- **Use appropriate unique keys** — Ensure each model can be matched correctly
- **Maintain consistent ordering** — Keep JSON files and model arrays in the same order
- **Consider dependencies** — If this seeder depends on others, specify them
- **Test thoroughly** — Verify that all models are imported correctly with your data

## Next Steps

- [Seeder Dependencies](./dependencies.md) — Order seeders correctly
- [Exporting Relations](./export-relations.md) — Export related data
- [Importing Data](../usage/importing.md) — Understand the upsert logic
