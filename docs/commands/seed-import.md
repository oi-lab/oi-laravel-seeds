---
title: seed:import
order: 3
---

# seed:import

Import seed data from JSON files into your database.

## Signature

```bash
php artisan seed:import {--seeder=}
```

## Options

### --seeder

Specify a single seeder to import (by class name or without namespace).

```bash
php artisan seed:import --seeder=UserSeeder
php artisan seed:import --seeder=Database\\Seeders\\UserSeeder
```

If not provided and `auto_discover` is enabled, all seeders are imported.

## Usage Examples

### Import a Single Seeder

```bash
php artisan seed:import --seeder=UserSeeder
```

Output:

```
Importing UserSeeder...
Importing from storage/app/private/seeders/users.json
Created: 3, Updated: 2
UserSeeder imported successfully.
```

### Import All Seeders

When `auto_discover` is enabled, import all seeders in dependency order:

```bash
php artisan seed:import
```

Output:

```
Discovering exportable seeders...
Importing TenantSeeder...
Importing from storage/app/private/seeders/tenants.json
Created: 2, Updated: 0
Importing RoleSeeder...
Importing from storage/app/private/seeders/roles.json
Created: 5, Updated: 0
Importing UserSeeder...
Importing from storage/app/private/seeders/users.json
Created: 8, Updated: 4
All seeders imported successfully.
```

## Dependency Ordering

The command respects seeder dependencies via topological sort.

If `UserSeeder` depends on `RoleSeeder`:

```php
protected array $dependencies = [RoleSeeder::class];
```

Running `php artisan seed:import` imports seeders in the correct order:

```
Importing RoleSeeder...
Importing UserSeeder...
```

This prevents foreign key constraint violations.

## Circular Dependency Detection

If seeder dependencies form a cycle, the command fails:

```
Error: Circular dependency detected between seeders
```

Fix circular dependencies in your seeder classes.

## Created vs Updated Count

The output shows how many records were created and how many were updated:

```
Created: 3, Updated: 2
```

- **Created** — New records inserted into the database
- **Updated** — Existing records updated based on `$uniqueBy` columns

Records are matched using the `updateOrCreate()` method with columns from `$uniqueBy`.

See [Importing Data](../usage/importing.md) for details on the upsert logic.

## Missing Files

If a JSON file is missing, the import fails with an error:

```
Error: File not found: storage/app/private/seeders/users.json
```

Create the JSON file before running import, or use `seed:export` to generate it from your database.

## Multiple Models

If a seeder handles multiple models:

```php
protected array $jsonFilename = ['users.json', 'profiles.json'];
protected array $modelClass = [User::class, Profile::class];
```

Both JSON files are imported in order:

```
Importing UserSeeder...
Importing from storage/app/private/seeders/users.json
Created: 5, Updated: 0
Importing from storage/app/private/seeders/profiles.json
Created: 5, Updated: 0
UserSeeder imported successfully.
```

## Upsert Logic

Records are imported using `updateOrCreate()` with the columns specified in `$uniqueBy`:

For `$uniqueBy = 'email'`:

```php
User::updateOrCreate(
    ['email' => $record['email']],
    [/* other fields */]
);
```

If a user with that email exists, it's updated. If not, a new user is created.

For composite keys (`$uniqueBy = ['tenant_id', 'email']`):

```php
User::updateOrCreate(
    ['tenant_id' => $record['tenant_id'], 'email' => $record['email']],
    [/* other fields */]
);
```

See [Importing Data](../usage/importing.md) for complete examples.

## Standard Seeding

You can also use Laravel's standard seeding to import data:

```bash
php artisan db:seed --class=UserSeeder
```

This runs the seeder's `run()` method, which calls `importData()`.

## Workflow Examples

### Seed a Fresh Database

```bash
php artisan migrate
php artisan seed:import
```

### Reset and Reseed

```bash
php artisan migrate:fresh
php artisan seed:import
```

### Update Existing Data

After exporting and modifying JSON files:

```bash
php artisan seed:import --seeder=UserSeeder
```

Existing records (matched by `$uniqueBy` columns) are updated. New records are created.

## Exit Codes

- **0** — Import successful
- **1** — Import failed (missing file, seeder not found, circular dependency, database error, etc.)

## Troubleshooting

### "File not found" Error

```
Error: File not found: storage/app/private/seeders/users.json
```

Create the JSON file:

```bash
php artisan seed:export --seeder=UserSeeder
```

Or create it manually in the configured storage path.

### "Circular dependency" Error

```
Error: Circular dependency detected between seeders
```

Check your seeder `$dependencies` for cycles:

```php
// UserSeeder
protected array $dependencies = [RoleSeeder::class];

// RoleSeeder
protected array $dependencies = [UserSeeder::class]; // CIRCULAR!
```

Remove the circular reference.

### Foreign Key Constraint Errors

If imports fail with foreign key errors, check seeder dependencies:

```php
// UserSeeder should depend on RoleSeeder
protected array $dependencies = [RoleSeeder::class];
```

Ensure seeders are ordered so dependencies are imported first.

## Next Steps

- [seed:export](./seed-export.md) — Generate JSON from your database
- [Importing Data](../usage/importing.md) — Understand the upsert logic
- [Seeder Dependencies](../advanced/dependencies.md) — Manage complex dependencies
