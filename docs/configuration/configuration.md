---
title: Configuration
order: 1
---

# Configuration

## Configuration File

After publishing, the configuration file is located at `config/oi-laravel-seeds.php`:

```php
<?php

return [
    'storage_path' => env('OI_SEEDS_STORAGE_PATH', 'storage/app/private/seeders'),
    'default_unique_by' => env('OI_SEEDS_DEFAULT_UNIQUE_BY', 'id'),
    'auto_discover' => env('OI_SEEDS_AUTO_DISCOVER', true),
    'json_options' => JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE,
];
```

## Configuration Options

### storage_path

The directory where JSON seed files are stored and read from.

- **Type:** `string`
- **Default:** `storage/app/private/seeders`
- **Environment Variable:** `OI_SEEDS_STORAGE_PATH`

This path is relative to your Laravel application root. Make sure this directory is writable if you plan to use the export command.

```php
'storage_path' => 'storage/app/seeds',
```

### default_unique_by

The default column(s) to use as the unique identifier when importing data via `updateOrCreate()`.

- **Type:** `string|array`
- **Default:** `id`
- **Environment Variable:** `OI_SEEDS_DEFAULT_UNIQUE_BY`

If a seeder doesn't explicitly define `$uniqueBy`, this default value is used.

```php
'default_unique_by' => 'email',
```

For composite unique keys:

```php
'default_unique_by' => ['tenant_id', 'slug'],
```

### auto_discover

Whether to automatically discover seeder classes when using the `seed:export` and `seed:import` commands without specifying a seeder.

- **Type:** `boolean`
- **Default:** `true`
- **Environment Variable:** `OI_SEEDS_AUTO_DISCOVER`

When enabled, the package scans `database/seeders/` for classes using the `ExportableSeeder` trait.

```php
'auto_discover' => false,
```

### json_options

PHP options passed to `json_encode()` when exporting data.

- **Type:** `int` (flags)
- **Default:** `JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE`

```php
'json_options' => JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
```

Common flags:

- `JSON_PRETTY_PRINT` — Format JSON with indentation
- `JSON_UNESCAPED_UNICODE` — Don't escape non-ASCII Unicode characters
- `JSON_UNESCAPED_SLASHES` — Don't escape forward slashes

## Environment Variables

You can override configuration using environment variables in your `.env` file:

```env
OI_SEEDS_STORAGE_PATH=storage/app/seeds
OI_SEEDS_DEFAULT_UNIQUE_BY=email
OI_SEEDS_AUTO_DISCOVER=true
```

## Directory Structure

Ensure your seed storage directory exists and is writable:

```bash
mkdir -p storage/app/private/seeders
chmod 755 storage/app/private/seeders
```

If using a custom path, create it before running export commands:

```bash
mkdir -p your/custom/path
```

## Gitignore Considerations

If your seed files contain sensitive data, add them to `.gitignore`:

```
storage/app/private/seeders/
```

If you want to version control seed files, ensure sensitive attributes are filtered using `getExportableAttributes()` in your seeders.
