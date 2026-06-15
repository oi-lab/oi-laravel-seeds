---
title: Commands
description: Overview of the Artisan commands shipped with OI Laravel Seeds
order: 1
---

# Commands

OI Laravel Seeds ships three Artisan commands: one generator to scaffold
exportable seeders, and two commands to move data between the database and JSON
files.

## Available Commands

### make:exportable-seeder

Generates a new seeder class that uses the `ExportableSeeder` trait, pre-filled
with the model, unique key, and JSON filename.

```bash
php artisan make:exportable-seeder UserSeeder --model=User
```

See [make:exportable-seeder](./make-seeder.md).

### seed:export

Exports records from the database to JSON files, resolving seeder dependencies
automatically.

```bash
php artisan seed:export
php artisan seed:export --seeder=UserSeeder --with-relations
```

See [seed:export](./seed-export.md).

### seed:import

Imports data from JSON files into the database using upsert logic, in dependency
order.

```bash
php artisan seed:import
php artisan seed:import --seeder=UserSeeder
```

See [seed:import](./seed-import.md).

## Next Steps

- [make:exportable-seeder](./make-seeder.md) — Scaffold an exportable seeder
- [seed:export](./seed-export.md) — Export database records to JSON
- [seed:import](./seed-import.md) — Import JSON data into the database
