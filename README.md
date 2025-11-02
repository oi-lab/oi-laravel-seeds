# OI Laravel Seeds

[![Latest Version on Packagist](https://img.shields.io/packagist/v/oi-lab/oi-laravel-seeds.svg?style=flat-square)](https://packagist.org/packages/oi-lab/oi-laravel-seeds)
[![Total Downloads](https://img.shields.io/packagist/dt/oi-lab/oi-laravel-seeds.svg?style=flat-square)](https://packagist.org/packages/oi-lab/oi-laravel-seeds)

A Laravel package to export and import seeders to/from JSON files with intelligent dependency management.

## Features

- Export database data to JSON files
- Import JSON files back to database with upsert support
- Automatic dependency resolution between seeders
- Support for exporting models with relations
- Artisan commands for easy management
- Generator command to create exportable seeders
- Configurable storage paths and options

## Installation

Install the package via composer:

```bash
composer require oi-lab/oi-laravel-seeds
```

Publish the configuration file (optional):

```bash
php artisan vendor:publish --tag=oi-seeds-config
```

Publish the stubs (optional):

```bash
php artisan vendor:publish --tag=oi-seeds-stubs
```

## Usage

### Creating an Exportable Seeder

Generate a new exportable seeder using the artisan command:

```bash
php artisan make:exportable-seeder GroupSeeder --model=Group --unique-by=name
```

Options:
- `--model` or `-m`: The model class to use for this seeder
- `--unique-by` or `-u`: The unique column(s) for upsert operations (default: id)
- `--json-filename` or `-j`: The JSON filename for export/import

This will create a seeder class like:

```php
<?php

namespace Database\Seeders;

use App\Models\Group;
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

### Defining Dependencies

If your seeder depends on other seeders, specify them in the `$dependencies` property:

```php
protected array $dependencies = [
    UserSeeder::class,
    RoleSeeder::class,
];
```

### Exporting Relations

To export models with their relations, define them in the `$exportRelations` property:

```php
protected array $exportRelations = ['roles', 'permissions'];
```

Then use the `--with-relations` flag when exporting:

```bash
php artisan seed:export --with-relations
```

### Exporting Data

Export all exportable seeders:

```bash
php artisan seed:export
```

Export a specific seeder:

```bash
php artisan seed:export --seeder=GroupSeeder
```

Export with relations:

```bash
php artisan seed:export --with-relations
```

### Importing Data

Import all exportable seeders:

```bash
php artisan seed:import
```

Import a specific seeder:

```bash
php artisan seed:import --seeder=GroupSeeder
```

### Customizing Exported Attributes

Override the `getExportableAttributes` method in your seeder to customize which attributes are exported:

```php
protected function getExportableAttributes(mixed $model): array
{
    $attributes = $model->toArray();

    // Remove sensitive data
    unset($attributes['password'], $attributes['remember_token']);

    return $attributes;
}
```

### Multiple Models per Seeder

You can export/import multiple models in a single seeder by using arrays:

```php
protected array $jsonFilename = ['users.json', 'profiles.json'];
protected array $modelClass = [User::class, Profile::class];
```

## Configuration

The configuration file `config/oi-seeds.php` allows you to customize:

- `storage_path`: Base storage path for JSON files (default: `app/private/seeders`)
- `default_unique_by`: Default column for upsert operations (default: `id`)
- `auto_discover`: Auto-discover exportable seeders (default: `true`)
- `json_options`: JSON encoding options (default: `JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE`)

## How it Works

1. **Export**: The package scans all your seeders for those using the `ExportableSeeder` trait
2. **Dependencies**: It resolves dependencies using topological sort to ensure correct order
3. **Export Data**: Each seeder exports its model data to a JSON file in the configured storage path
4. **Import**: When importing, dependencies are processed first, then data is upserted using `updateOrCreate`

## Storage Location

By default, JSON files are stored in `storage/app/private/seeders/`. You can change this in the configuration:

```php
'storage_path' => env('OI_SEEDS_STORAGE_PATH', 'app/private/seeders'),
```

## Advanced Usage

### Custom Unique Keys

Use multiple columns for unique constraints:

```php
protected array $uniqueBy = ['email', 'tenant_id'];
```

### Conditional Export

You can add conditions to your export query by overriding the `exportData` method:

```php
public function exportData(bool $withRelations = false): int
{
    // Add custom logic here
    return parent::exportData($withRelations);
}
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email contact@oi-lab.com instead of using the issue tracker.

## Credits

**[Olivier Lacombe](https://www.olacombe.com)** - Creator and maintainer

Olivier is a Product & Technology Director based in Montpellier, France, with over 20 years of experience innovating in UX/UI and emerging technologies. He specializes in guiding enterprises toward cutting-edge digital solutions, combining user-centered design with continuous optimization and artificial intelligence integration.

**Projects & Resources:**
- [OnAI](https://onai.olacombe.com) - Training courses and masterclasses on generative AI for businesses
- [Promptr](https://promptr.olacombe.com) - Prompt engineering Management Platform

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
