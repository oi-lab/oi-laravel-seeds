---
title: Exporting Data
order: 2
---

# Exporting Data

The `exportData()` method exports records from your database to JSON files. It's typically called via the `seed:export` command, but can also be called directly.

## Basic Export

```php
public function exportData(bool $withRelations = false): int
```

Export returns the total number of records exported.

### Via Command

Export records for a specific seeder:

```bash
php artisan seed:export --seeder=UserSeeder
```

Export all seeders:

```bash
php artisan seed:export
```

Export with relations included:

```bash
php artisan seed:export --seeder=UserSeeder --with-relations
```

### Via Code

In tests or elsewhere, call `exportData()` directly:

```php
$seeder = new UserSeeder();
$count = $seeder->exportData();
```

## Filtering Sensitive Data

By default, all model attributes are exported. Override `getExportableAttributes()` to remove sensitive fields:

```php
protected function getExportableAttributes(mixed $model): array
{
    return [
        'id',
        'name',
        'email',
        'email_verified_at',
        'created_at',
        'updated_at',
    ];
}
```

This removes `password` and `remember_token` from the export.

### Complete Example

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use OiLab\OiLaravelSeeds\Traits\ExportableSeeder;

class UserSeeder extends Seeder
{
    use ExportableSeeder;

    protected string $jsonFilename = 'users.json';

    protected string $modelClass = User::class;

    public function run(): void
    {
        $this->importData();
    }

    protected function getExportableAttributes(mixed $model): array
    {
        return [
            'id',
            'name',
            'email',
            'email_verified_at',
            'is_admin',
            'created_at',
            'updated_at',
        ];
    }
}
```

When you run `php artisan seed:export --seeder=UserSeeder`, the JSON file will only contain these attributes.

## JSON Output Format

The exported JSON file is formatted with pretty printing and unescaped Unicode characters (configured in `config/oi-laravel-seeds.php`).

### Example Output

For the seeder above, `storage/app/private/seeders/users.json` looks like:

```json
[
    {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "email_verified_at": "2025-01-01T00:00:00.000000Z",
        "is_admin": true,
        "created_at": "2025-01-01T00:00:00.000000Z",
        "updated_at": "2025-01-01T00:00:00.000000Z"
    },
    {
        "id": 2,
        "name": "Jane Smith",
        "email": "jane@example.com",
        "email_verified_at": "2025-01-02T00:00:00.000000Z",
        "is_admin": false,
        "created_at": "2025-01-02T00:00:00.000000Z",
        "updated_at": "2025-01-02T00:00:00.000000Z"
    }
]
```

## Exporting with Relations

When exporting with relations, related data is nested in the JSON:

### Seeder Configuration

```php
protected array $exportRelations = ['posts', 'comments'];
```

### Export Command

```bash
php artisan seed:export --seeder=UserSeeder --with-relations
```

### Example Output

```json
[
    {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "created_at": "2025-01-01T00:00:00.000000Z",
        "updated_at": "2025-01-01T00:00:00.000000Z",
        "posts": [
            {
                "id": 1,
                "title": "First Post",
                "content": "Hello world",
                "created_at": "2025-01-01T00:00:00.000000Z",
                "updated_at": "2025-01-01T00:00:00.000000Z"
            }
        ],
        "comments": [
            {
                "id": 10,
                "body": "Great post!",
                "created_at": "2025-01-01T00:00:00.000000Z",
                "updated_at": "2025-01-01T00:00:00.000000Z"
            }
        ]
    }
]
```

## Important Notes

- Exporting relations includes the nested data in JSON but **does not affect imports**. When importing, only the parent model is created/updated.
- Relations must be defined as methods on your model (e.g., `public function posts(): HasMany`).
- Use `getExportableAttributes()` to filter both parent and relation attributes.

## Multiple Models

When a seeder handles multiple models, each gets its own export:

```php
protected array $jsonFilename = ['users.json', 'profiles.json'];
protected array $modelClass = [User::class, Profile::class];
```

The export command exports records for both models to separate files.

## Next Steps

- [Importing Data](./importing.md) — Learn how imports work with the upsert logic
- [Configuration](../configuration/configuration.md) — Customize JSON output formatting
