# OI Laravel Seeds — AI Context

This package exports and imports Laravel seeder data to and from JSON files, with automatic dependency
resolution between seeders. It turns a regular `Seeder` into a portable, version-controllable data fixture
that can round-trip through the database.

## Core Concepts

- **ExportableSeeder** — the trait a seeder uses to gain `exportData()` / `importData()`. The host seeder
  declares what to export through typed properties.
- **Export** — reads model rows from the database and writes them to JSON files under the configured
  storage path.
- **Import** — reads those JSON files back and upserts rows with `updateOrCreate`, keyed by `$uniqueBy`.
- **Dependencies** — seeders declare other seeders they depend on; the package runs them first using a
  topological sort, so foreign-key order is always correct.

## Making a Seeder Exportable

Generate one with the artisan command:

```bash
php artisan make:exportable-seeder GroupSeeder --model=Group --unique-by=name
```

This produces a seeder using the trait:

```php
use Illuminate\Database\Seeder;
use OiLab\OiLaravelSeeds\Traits\ExportableSeeder;

class GroupSeeder extends Seeder
{
    use ExportableSeeder;

    protected string $jsonFilename = 'groups.json';
    protected string $modelClass = Group::class;
    protected array $dependencies = [];
    protected string $uniqueBy = 'name';
    protected array $exportRelations = [];

    public function run(): void
    {
        $this->importData();
    }
}
```

`run()` calls `importData()`, so `php artisan db:seed` populates the database from the committed JSON.

## Declaring Properties

| Property | Type | Purpose |
|----------|------|---------|
| `$jsonFilename` | `string\|array` | JSON file(s) the seeder reads/writes |
| `$modelClass` | `string\|array` | Eloquent model(s) to export/import |
| `$uniqueBy` | `string\|array` | Column(s) used for the upsert key |
| `$dependencies` | `array` | Seeder classes that must run first |
| `$exportRelations` | `array` | Relations eager-loaded when exporting `--with-relations` |

## Exporting & Importing

```bash
php artisan seed:export                      # export every exportable seeder
php artisan seed:export --seeder=GroupSeeder # export one seeder
php artisan seed:export --with-relations     # include declared $exportRelations

php artisan seed:import                       # import every exportable seeder
php artisan seed:import --seeder=GroupSeeder  # import one seeder
```

Dependencies are resolved automatically on both export and import, so you never have to order seeders by hand.

## Common Patterns

- **Multiple models per seeder** — set `$jsonFilename` and `$modelClass` to parallel arrays.
- **Composite unique keys** — set `$uniqueBy` to an array (e.g. `['email', 'tenant_id']`).
- **Strip sensitive data** — override `getExportableAttributes(mixed $model): array` to remove columns such
  as `password` or `remember_token` before they land in JSON.
- **Custom export query** — override `exportData(bool $withRelations = false): int` and call
  `parent::exportData()` after adding constraints.

## Configuration

Publish the config and stubs (both optional):

```bash
php artisan vendor:publish --tag=oi-laravel-seeds-config
php artisan vendor:publish --tag=oi-laravel-seeds-stubs
```

`config/oi-laravel-seeds.php` exposes:

| Key | Default | Description |
|-----|---------|-------------|
| `storage_path` | `app/private/seeders` | Base path for JSON files, relative to `storage_path()` |
| `default_unique_by` | `id` | Upsert column used when a seeder omits `$uniqueBy` |
| `auto_discover` | `true` | Auto-discover exportable seeders in `database/seeders` |
| `json_options` | `JSON_PRETTY_PRINT \| JSON_UNESCAPED_UNICODE` | Encoding flags for written JSON |

## Conventions

- Exported JSON lives under `storage/app/private/seeders/` by default — commit it to version control to
  share fixtures across environments.
- Import is idempotent: it upserts by `$uniqueBy`, so re-running never duplicates rows.
- Keep `run()` calling `importData()` so the standard `db:seed` flow stays in sync with the JSON files.

## Updating the AI Skill

After updating this package, re-install the skill files:

```bash
php artisan oi:skills
```
