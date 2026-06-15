---
title: Installation
order: 2
---

# Installation

## Install via Composer

```bash
composer require oi-lab/oi-laravel-seeds
```

## Publish Configuration

Publish the package configuration to your application:

```bash
php artisan vendor:publish --tag=oi-laravel-seeds-config
```

This creates a new configuration file at `config/oi-laravel-seeds.php`.

## Quick Start

### 1. Generate an Exportable Seeder

Create a new seeder using the `make:exportable-seeder` command:

```bash
php artisan make:exportable-seeder UserSeeder --model=App\\Models\\User
```

This generates a seeder class at `database/seeders/UserSeeder.php`:

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

### 2. Create Your JSON Seed Data

Create a JSON file at `storage/app/private/seeders/users.json`:

```json
[
    {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "password": "$2y$12$...",
        "created_at": "2025-01-01T00:00:00Z",
        "updated_at": "2025-01-01T00:00:00Z"
    },
    {
        "id": 2,
        "name": "Jane Smith",
        "email": "jane@example.com",
        "password": "$2y$12$...",
        "created_at": "2025-01-01T00:00:00Z",
        "updated_at": "2025-01-01T00:00:00Z"
    }
]
```

### 3. Run the Seeder

Run your seeder using Laravel's standard command:

```bash
php artisan db:seed --class=UserSeeder
```

Or seed all seeders:

```bash
php artisan db:seed
```

### 4. Export Updated Data

When your database changes and you want to update the JSON file, use the export command:

```bash
php artisan seed:export --seeder=UserSeeder
```

The JSON file is automatically updated with the current database records.

## Next Steps

- [Configuration](../configuration/_index.md) — Learn about available configuration options
- [The ExportableSeeder Trait](../usage/_index.md) — Understand all available trait features
- [Commands](../commands/make-seeder.md) — Full command reference
