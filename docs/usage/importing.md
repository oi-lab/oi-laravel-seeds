---
title: Importing Data
order: 3
---

# Importing Data

The `importData()` method reads JSON files and imports records into the database using Laravel's `updateOrCreate()` method, which provides upsert (update or create) logic.

## How It Works

`importData()` is called from your seeder's `run()` method:

```php
public function run(): void
{
    $this->importData();
}
```

For each record in the JSON file:

1. Extracts the columns specified in `$uniqueBy` as the WHERE clause
2. Uses all remaining columns as the values to update/create
3. Calls `Model::updateOrCreate($where, $values)`

## The Upsert Logic

### Single Unique Column

When `$uniqueBy = 'email'`:

```php
protected string $uniqueBy = 'email';
```

Each JSON record is processed like this:

```php
User::updateOrCreate(
    ['email' => $record['email']],  // WHERE clause
    [
        'name' => $record['name'],
        'created_at' => $record['created_at'],
        'updated_at' => $record['updated_at'],
    ]
);
```

If a user with that email exists, it's updated. If not, a new user is created with all fields.

### Composite Unique Keys

When `$uniqueBy = ['tenant_id', 'email']`:

```php
protected array $uniqueBy = ['tenant_id', 'email'];
```

Each JSON record is processed like this:

```php
User::updateOrCreate(
    [
        'tenant_id' => $record['tenant_id'],
        'email' => $record['email'],
    ],
    [
        'name' => $record['name'],
        'phone' => $record['phone'],
        'created_at' => $record['created_at'],
        'updated_at' => $record['updated_at'],
    ]
);
```

Records are uniquely identified by the combination of tenant_id and email.

## Example

### JSON File (users.json)

```json
[
    {
        "id": 1,
        "email": "john@example.com",
        "name": "John Doe",
        "phone": "+1-555-0100",
        "created_at": "2025-01-01T00:00:00Z",
        "updated_at": "2025-01-01T00:00:00Z"
    },
    {
        "id": 2,
        "email": "jane@example.com",
        "name": "Jane Smith",
        "phone": "+1-555-0101",
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
use Illuminate\Database\Seeder;
use OiLab\OiLaravelSeeds\Traits\ExportableSeeder;

class UserSeeder extends Seeder
{
    use ExportableSeeder;

    protected string $jsonFilename = 'users.json';

    protected string $modelClass = User::class;

    protected string $uniqueBy = 'email';

    public function run(): void
    {
        $this->importData();
    }
}
```

### Execution

When you run `php artisan db:seed --class=UserSeeder`:

1. **First run:** Both users are created with IDs 1 and 2
2. **Second run:** 
   - John's record is found by email and updated (if any fields changed)
   - Jane's record is found by email and updated (if any fields changed)
   - No duplicates are created

## Multiple Models

A single seeder can import data for multiple models:

```php
protected array $jsonFilename = ['users.json', 'profiles.json'];

protected array $modelClass = [User::class, Profile::class];

protected string $uniqueBy = 'id';
```

The `importData()` method processes each pair in order:
1. Import records from `users.json` into the `User` model
2. Import records from `profiles.json` into the `Profile` model

## Missing Unique Key Handling

If a record is missing a column specified in `$uniqueBy`, that record is skipped with a warning:

```php
protected array $uniqueBy = ['tenant_id', 'email'];
```

If a record in the JSON lacks either `tenant_id` or `email`, it cannot be uniquely identified and is skipped.

## Relations in Imported Data

When importing, nested relation data in JSON is ignored. Only the parent model is imported:

```json
[
    {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "posts": [
            {
                "id": 1,
                "title": "First Post"
            }
        ]
    }
]
```

Only the user record is created/updated. The posts are not automatically imported.

If you need to import relations, create separate seeders for each model.

## Via Command

Import data using the Artisan command:

```bash
php artisan seed:import --seeder=UserSeeder
```

Import all seeders (respecting dependency order):

```bash
php artisan seed:import
```

## Next Steps

- [Seeder Dependencies](../advanced/dependencies.md) — Understand dependency ordering
- [Multiple Models](../advanced/multiple-models.md) — Import multiple models per seeder
- [Commands](../commands/seed-import.md) — Full seed:import command reference
