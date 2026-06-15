---
title: Seeder Dependencies
order: 1
---

# Seeder Dependencies

The `$dependencies` property allows you to specify which seeders must run before the current seeder. This is essential when seeders have foreign key relationships or need to create data in a specific order.

## Defining Dependencies

Specify dependent seeders using the `$dependencies` property:

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

    protected array $dependencies = [
        RoleSeeder::class,
        TenantSeeder::class,
    ];

    public function run(): void
    {
        $this->importData();
    }
}
```

In this example, `RoleSeeder` and `TenantSeeder` must run before `UserSeeder`.

## Topological Sorting

When you run `php artisan seed:import` (without specifying a single seeder), the package uses **depth-first topological sort** to order all seeders based on their dependencies.

### Example Dependency Chain

Consider four seeders with the following relationships:

```
TenantSeeder (no dependencies)
    ↓
RoleSeeder (depends on Tenant)
    ↓
UserSeeder (depends on Role and Tenant)
    ↓
CompanySeeder (depends on User)
```

### Seeder Configuration

```php
// TenantSeeder.php
protected array $dependencies = [];

// RoleSeeder.php
protected array $dependencies = [TenantSeeder::class];

// UserSeeder.php
protected array $dependencies = [RoleSeeder::class, TenantSeeder::class];

// CompanySeeder.php
protected array $dependencies = [UserSeeder::class];
```

### Execution Order

When you run `php artisan seed:import`, seeders execute in this order:

```
1. TenantSeeder    (no dependencies)
2. RoleSeeder      (depends on Tenant)
3. UserSeeder      (depends on Role and Tenant)
4. CompanySeeder   (depends on User)
```

The command respects all dependency constraints.

## Circular Dependency Detection

If you create circular dependencies, the package detects and reports an error:

```php
// UserSeeder.php
protected array $dependencies = [RoleSeeder::class];

// RoleSeeder.php
protected array $dependencies = [UserSeeder::class]; // CIRCULAR!
```

Running `php artisan seed:import` fails:

```
Error: Circular dependency detected between seeders
```

Remove the circular reference to fix this.

## Multiple Dependencies

A seeder can depend on multiple other seeders:

```php
protected array $dependencies = [
    TenantSeeder::class,
    RoleSeeder::class,
    DepartmentSeeder::class,
];
```

All dependencies must complete before this seeder runs.

## Real-World Example

Consider a multi-tenant SaaS application with the following structure:

```
Tenants → Roles → Users → Permissions → Audits
```

### TenantSeeder

```php
<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;
use OiLab\OiLaravelSeeds\Traits\ExportableSeeder;

class TenantSeeder extends Seeder
{
    use ExportableSeeder;

    protected string $jsonFilename = 'tenants.json';

    protected string $modelClass = Tenant::class;

    protected array $dependencies = []; // No dependencies

    public function run(): void
    {
        $this->importData();
    }
}
```

### RoleSeeder

```php
<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use OiLab\OiLaravelSeeds\Traits\ExportableSeeder;

class RoleSeeder extends Seeder
{
    use ExportableSeeder;

    protected string $jsonFilename = 'roles.json';

    protected string $modelClass = Role::class;

    protected array $dependencies = [TenantSeeder::class];

    public function run(): void
    {
        $this->importData();
    }
}
```

### UserSeeder

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

    protected array $dependencies = [
        TenantSeeder::class,
        RoleSeeder::class,
    ];

    public function run(): void
    {
        $this->importData();
    }
}
```

### PermissionSeeder

```php
<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use OiLab\OiLaravelSeeds\Traits\ExportableSeeder;

class PermissionSeeder extends Seeder
{
    use ExportableSeeder;

    protected string $jsonFilename = 'permissions.json';

    protected string $modelClass = Permission::class;

    protected array $dependencies = [RoleSeeder::class];

    public function run(): void
    {
        $this->importData();
    }
}
```

## Execution

When you run:

```bash
php artisan seed:import
```

Seeders execute in order:

```
Importing TenantSeeder...
Imported 2 tenants
Importing RoleSeeder...
Imported 5 roles
Importing UserSeeder...
Imported 12 users
Importing PermissionSeeder...
Imported 20 permissions
All seeders imported successfully.
```

## Why Dependencies Matter

### Preventing Foreign Key Errors

Without proper ordering, you get constraint violations:

```
SQLSTATE[HY000]: General error: 1452 Cannot add or update a child row: a foreign key constraint fails
```

With dependencies, foreign keys are properly ordered.

### Deterministic Seeding

Dependencies ensure that no matter the order seeders are discovered, they execute in the correct order.

## Database Migrations Aren't Affected

Seeder dependencies only order seeders relative to each other. Database migrations still run before any seeders. Ensure your migrations create tables in the correct order separately.

## Next Steps

- [Multiple Models](./multiple-models.md) — Handle multiple models in one seeder
- [Importing Data](../usage/importing.md) — Understand the upsert logic
- [seed:import Command](../commands/seed-import.md) — Full command reference
