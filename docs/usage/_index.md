---
title: The ExportableSeeder Trait
order: 1
---

# The ExportableSeeder Trait

The `ExportableSeeder` trait is the core of OI Laravel Seeds. It adds export and import functionality to any Laravel Seeder class.

## Using the Trait

Add the trait to your seeder class:

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use OiLab\OiLaravelSeeds\Traits\ExportableSeeder;

class UserSeeder extends Seeder
{
    use ExportableSeeder;
    // ... trait configuration below
}
```

## Required Properties

### $jsonFilename

The path(s) to the JSON file(s) containing seed data, relative to the configured storage path.

- **Type:** `string` or `array`
- **Required:** Yes

For a single model:

```php
protected string $jsonFilename = 'users.json';
```

For multiple models:

```php
protected array $jsonFilename = ['users.json', 'profiles.json'];
```

### $modelClass

The Eloquent model class(es) to import data into.

- **Type:** `string` or `array`
- **Required:** Yes

For a single model:

```php
protected string $modelClass = User::class;
```

For multiple models:

```php
protected array $modelClass = [User::class, Profile::class];
```

When using arrays, both arrays must have the same number of elements, and they are paired by index.

## Optional Properties

### $uniqueBy

The column(s) used to determine if a record should be updated or created.

- **Type:** `string` or `array`
- **Default:** `id` (or the configured `default_unique_by`)

For a single unique column:

```php
protected string $uniqueBy = 'email';
```

For composite unique keys:

```php
protected array $uniqueBy = ['tenant_id', 'email'];
```

This value is passed directly to `updateOrCreate()`.

### $dependencies

An array of seeder class names that must run before this seeder.

- **Type:** `array`
- **Default:** `[]`

```php
protected array $dependencies = [
    TenantSeeder::class,
    RoleSeeder::class,
];
```

This enables automatic dependency ordering via topological sort.

### $exportRelations

An array of relation method names to include when exporting data.

- **Type:** `array`
- **Default:** `[]`

```php
protected array $exportRelations = ['posts', 'comments'];
```

Relations are only exported when using `seed:export --with-relations`.

## Required Methods

### run()

The `run()` method must call `$this->importData()` to import seed data:

```php
public function run(): void
{
    $this->importData();
}
```

## Complete Example

Here's a complete seeder class demonstrating all features:

```php
<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use OiLab\OiLaravelSeeds\Traits\ExportableSeeder;

class UserSeeder extends Seeder
{
    use ExportableSeeder;

    protected string $jsonFilename = 'users.json';

    protected string $modelClass = User::class;

    protected string $uniqueBy = 'email';

    protected array $dependencies = [
        RoleSeeder::class,
    ];

    protected array $exportRelations = ['roles'];

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
            'created_at',
            'updated_at',
        ];
    }
}
```

## Trait Methods

### importData(): void

Reads the JSON file(s) and imports records into the database using `updateOrCreate()`.

Called from your `run()` method:

```php
public function run(): void
{
    $this->importData();
}
```

### exportData(bool $withRelations = false): int

Exports records from the database to JSON file(s). Returns the number of records exported.

Called via the `seed:export` command, or directly in tests:

```php
$exported = $this->exportData();
$exported = $this->exportData(withRelations: true);
```

### getExportableAttributes(mixed $model): array

Customize which attributes are included in exports. Override this method to filter sensitive data.

Default behavior exports all model attributes:

```php
protected function getExportableAttributes(mixed $model): array
{
    return $model->getAttributes();
}
```

See [Exporting Data](./exporting.md) for more details.

## Next Steps

- [Exporting Data](./exporting.md) — Learn how to export and customize exports
- [Importing Data](./importing.md) — Understand the upsert logic
- [Seeder Dependencies](../advanced/dependencies.md) — Manage complex seeder orderings
