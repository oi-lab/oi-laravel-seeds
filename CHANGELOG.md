# Changelog

All notable changes to `oi-laravel-seeds` will be documented in this file.

## [Unreleased]

### Added
- **AI Assistant Skills**: Bundled a Claude Code / JetBrains Junie skill describing how to use the package.
  Install it with the unified `php artisan oi:skills` command (provided by `oi-lab/oi-laravel-development`),
  or with the deprecated package-local `php artisan oi-seeds:install-ai-skill` fallback. The canonical source
  lives in `resources/stubs/ai-skill.md` and is synced to the committed copies via `composer sync-ai-skills`.

## [1.0.0]

Initial release of OI Laravel Seeds — export and import Laravel seeder data to/from JSON files.

### Core Features
- **ExportableSeeder trait**: Turns any seeder into a portable, JSON-backed data fixture.
- **Export / Import commands**: `seed:export` and `seed:import` round-trip model data through JSON files.
- **Generator command**: `make:exportable-seeder` scaffolds an exportable seeder with the right properties.
- **Dependency resolution**: Seeder `$dependencies` are ordered automatically via topological sort.
- **Relations & multiple models**: Export with `--with-relations`, or export several models from one seeder.
- **Idempotent imports**: Rows are upserted by `$uniqueBy`, so re-running never duplicates data.
- **Configurable**: Storage path, default unique key, auto-discovery, and JSON encoding options.

### Requirements
- PHP 8.2, 8.3, or 8.4
- Laravel 11.0+ or 12.0+
