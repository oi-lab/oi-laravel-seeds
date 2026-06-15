---
title: seed:export
order: 2
---

# seed:export

Export records from the database to JSON seed files.

## Signature

```bash
php artisan seed:export {--seeder=} {--with-relations}
```

## Options

### --seeder

Specify a single seeder to export (by class name or without namespace).

```bash
php artisan seed:export --seeder=UserSeeder
php artisan seed:export --seeder=Database\\Seeders\\UserSeeder
```

If not provided and `auto_discover` is enabled, all seeders are exported.

### --with-relations

Include related data when exporting. Relations are embedded in the JSON output.

```bash
php artisan seed:export --with-relations
php artisan seed:export --seeder=UserSeeder --with-relations
```

Relations are exported as nested arrays in the JSON file. See [Exporting Relations](../advanced/export-relations.md) for details.

## Usage Examples

### Export a Single Seeder

```bash
php artisan seed:export --seeder=UserSeeder
```

Output:

```
Exporting UserSeeder...
Exported 5 records to storage/app/private/seeders/users.json
```

### Export All Seeders

When `auto_discover` is enabled in config, export all seeders:

```bash
php artisan seed:export
```

Output:

```
Discovering exportable seeders...
Exporting TenantSeeder...
Exported 3 records to storage/app/private/seeders/tenants.json
Exporting RoleSeeder...
Exported 5 records to storage/app/private/seeders/roles.json
Exporting UserSeeder...
Exported 12 records to storage/app/private/seeders/users.json
All seeders exported successfully.
```

The order depends on seeder dependencies (topological sort).

### Export with Relations

Export a seeder including its relations:

```bash
php artisan seed:export --seeder=UserSeeder --with-relations
```

Output:

```
Exporting UserSeeder with relations...
Exported 5 records (with relations) to storage/app/private/seeders/users.json
```

Example JSON with relations (`posts` and `comments`):

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
                "id": 10,
                "title": "My First Post",
                "content": "Hello world",
                "created_at": "2025-01-01T00:00:00.000000Z",
                "updated_at": "2025-01-01T00:00:00.000000Z"
            }
        ],
        "comments": [
            {
                "id": 100,
                "body": "Great article!",
                "created_at": "2025-01-02T00:00:00.000000Z",
                "updated_at": "2025-01-02T00:00:00.000000Z"
            }
        ]
    }
]
```

## Export Respects Dependencies

When exporting all seeders, the command respects seeder dependencies via topological sort:

If `UserSeeder` depends on `RoleSeeder`:

```php
protected array $dependencies = [RoleSeeder::class];
```

Running `php artisan seed:export` exports seeders in the correct order:

```
Exporting RoleSeeder...
Exporting UserSeeder...
```

This ensures seeders with dependencies are exported after their dependencies.

## Dependency Resolution

If seeder dependencies form a cycle, the command fails with an error:

```
Error: Circular dependency detected between seeders
```

Fix circular dependencies before exporting.

## File Output

Exported JSON files are written to the configured storage path (default: `storage/app/private/seeders/`).

Files are overwritten if they already exist.

### JSON Format

By default, JSON is formatted with:
- Pretty printing (indentation)
- Unescaped Unicode characters
- All model attributes except those filtered by `getExportableAttributes()`

Configure JSON formatting in `config/oi-laravel-seeds.php`:

```php
'json_options' => JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE,
```

## Exit Codes

- **0** — Export successful
- **1** — Export failed (missing seeder, file write error, circular dependency, etc.)

## Filtering Data

Customize which attributes are exported by overriding `getExportableAttributes()` in your seeder:

```php
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
```

This removes sensitive fields like `password` and `remember_token` from exports.

See [Exporting Data](../usage/exporting.md) for more details.

## Common Workflows

### Update Seed Files After Database Changes

```bash
# Make changes to your database
php artisan seed:export --seeder=UserSeeder
```

### Version Control Seed Data

Export all seeders and commit to version control:

```bash
php artisan seed:export
git add storage/app/private/seeders/
git commit -m "Update seed data"
```

### Backup Production Data (with caution)

```bash
php artisan seed:export --with-relations > backup.json
```

## Next Steps

- [seed:import](./seed-import.md) — Import seed data from JSON
- [Exporting Data](../usage/exporting.md) — Learn filtering and customization
- [Exporting Relations](../advanced/export-relations.md) — Export related data
