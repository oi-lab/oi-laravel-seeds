---
title: make:exportable-seeder
order: 1
---

# make:exportable-seeder

Generate a new seeder class with the `ExportableSeeder` trait.

## Signature

```bash
php artisan make:exportable-seeder {name} {--model=} {--unique-by=} {--json-filename=}
```

## Arguments

### name

The name of the seeder class to create (required).

```bash
php artisan make:exportable-seeder UserSeeder
php artisan make:exportable-seeder ProductSeeder
php artisan make:exportable-seeder CompanySeeder
```

The class is created in `database/seeders/{name}.php`.

## Options

### --model

Specify the Eloquent model class that this seeder imports into.

```bash
php artisan make:exportable-seeder UserSeeder --model=App\\Models\\User
php artisan make:exportable-seeder UserSeeder --model=User
```

If provided, it's automatically added to the seeder class.

### --unique-by

Set the unique column(s) for upserting records.

```bash
php artisan make:exportable-seeder UserSeeder --unique-by=email
php artisan make:exportable-seeder ProductSeeder --unique-by=sku
```

For composite keys, use comma-separated values:

```bash
php artisan make:exportable-seeder TenantUserSeeder --unique-by=tenant_id,email
```

### --json-filename

Specify the JSON filename (relative to the storage path).

```bash
php artisan make:exportable-seeder UserSeeder --json-filename=users.json
php artisan make:exportable-seeder UserSeeder --json-filename=seeds/users.json
```

If not provided, the filename defaults to the snake_case plural form of the model name plus `.json`:

- `User` → `users.json`
- `Product` → `products.json`
- `CompanyProfile` → `company_profiles.json`

## Examples

### Basic Seeder

```bash
php artisan make:exportable-seeder UserSeeder --model=User
```

Generated file at `database/seeders/UserSeeder.php`:

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

    protected string $uniqueBy = 'id';

    public function run(): void
    {
        $this->importData();
    }
}
```

### With Custom Unique Column

```bash
php artisan make:exportable-seeder UserSeeder --model=User --unique-by=email
```

Generated file sets `$uniqueBy = 'email'`.

### With Custom JSON Filename

```bash
php artisan make:exportable-seeder RoleSeeder --model=Role --json-filename=roles.json
```

Generated file sets `$jsonFilename = 'roles.json'`.

### With Composite Key

```bash
php artisan make:exportable-seeder TenantUserSeeder --model=TenantUser --unique-by=tenant_id,user_id
```

Generated file sets `$uniqueBy = ['tenant_id', 'user_id']`.

## Generated Class Structure

The generated seeder class includes:

- `ExportableSeeder` trait
- `$jsonFilename` property — the JSON file to read from
- `$modelClass` property — the model to import into
- `$uniqueBy` property — the unique identifier column(s)
- `run()` method — calls `$this->importData()`

The generated seeder is ready to use immediately. You can:

1. Create the JSON file in your storage path
2. Run `php artisan db:seed --class=UserSeeder`

Or generate JSON from your database:

1. Add records to your database
2. Run `php artisan seed:export --seeder=UserSeeder`
3. Check the generated JSON file

## Next Steps

- [Installation](../getting-started/installation.md) — See the quick start workflow
- [The ExportableSeeder Trait](../usage/_index.md) — Understand all available properties and methods
- [Commands](./seed-export.md) — Learn about seed:export and seed:import
