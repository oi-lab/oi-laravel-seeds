# Laravel Seeds

Use the `oi-laravel-seeds` package to export and import seeder data to/from JSON files. Add the
`OiLab\OiLaravelSeeds\Traits\ExportableSeeder` trait to a seeder, declare `$jsonFilename`, `$modelClass`,
`$uniqueBy`, `$dependencies`, and `$exportRelations`, then use `php artisan seed:export` / `seed:import`.
Dependencies are resolved automatically via topological sort, and imports upsert by `$uniqueBy` so they
stay idempotent. Scaffold seeders with `php artisan make:exportable-seeder`.

- IMPORTANT: Activate `oilab-laravel-seeds` when exporting/importing seed data, building portable database
  fixtures, or working with JSON-backed seeders in this Laravel application.
