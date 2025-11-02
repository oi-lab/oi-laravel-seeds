<?php

namespace OiLab\OiLaravelSeeds\Traits;

use Illuminate\Support\Facades\File;

/**
 * Trait for seeders that support export/import from JSON files.
 *
 * Classes using this trait must define:
 * - protected string|array $jsonFilename - JSON filename(s) to export/import
 * - protected string|array $modelClass - Eloquent model class(es) to export/import
 * - protected array|string $uniqueBy - Column(s) to use for upsert
 * - protected array $dependencies - Seeders that must be executed before this one (default: [])
 * - protected array $exportRelations - Relations to include when exporting (default: [])
 */
trait ExportableSeeder
{
    /**
     * Export data from database to JSON file(s).
     */
    public function exportData(bool $withRelations = false): int
    {
        $totalExported = 0;

        // Handle single or multiple files
        $filenames = is_array($this->jsonFilename) ? $this->jsonFilename : [$this->jsonFilename];
        $modelClasses = is_array($this->modelClass) ? $this->modelClass : [$this->modelClass];

        foreach ($filenames as $index => $filename) {
            $modelClass = $modelClasses[$index] ?? $modelClasses[0];
            $count = $this->exportSingleModel($modelClass, $filename, $withRelations);
            $totalExported += $count;

            $this->command->info("Exported {$count} record(s) to {$filename}");
        }

        return $totalExported;
    }

    /**
     * Export a single model to JSON file.
     */
    protected function exportSingleModel(string $modelClass, string $filename, bool $withRelations): int
    {
        $path = $this->getStoragePath($filename);

        // Ensure directory exists
        $directory = dirname($path);
        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        // Get data from model
        $query = $modelClass::query();

        $exportRelations = property_exists($this, 'exportRelations') ? $this->exportRelations : [];

        if ($withRelations && ! empty($exportRelations)) {
            $query->with($exportRelations);
        }

        $data = $query->get();

        // Get exportable attributes
        $exportedData = $data->map(function ($item) {
            return $this->getExportableAttributes($item);
        })->toArray();

        // Write to JSON file
        File::put($path, json_encode($exportedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return count($exportedData);
    }

    /**
     * Import data from JSON file(s) to database.
     */
    public function importData(): void
    {
        // Handle single or multiple files
        $filenames = is_array($this->jsonFilename) ? $this->jsonFilename : [$this->jsonFilename];
        $modelClasses = is_array($this->modelClass) ? $this->modelClass : [$this->modelClass];

        foreach ($filenames as $index => $filename) {
            $modelClass = $modelClasses[$index] ?? $modelClasses[0];
            $this->importSingleModel($modelClass, $filename);
        }
    }

    /**
     * Import a single model from JSON file.
     */
    protected function importSingleModel(string $modelClass, string $filename): void
    {
        $path = $this->getStoragePath($filename);

        if (! File::exists($path)) {
            $this->command->error("File not found: {$path}");

            return;
        }

        $jsonData = File::get($path);
        $data = json_decode($jsonData, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error("Invalid JSON in {$filename}: ".json_last_error_msg());

            return;
        }

        $this->command->info("Importing from {$filename}");

        $created = 0;
        $updated = 0;

        foreach ($data as $record) {
            $uniqueKeys = is_array($this->uniqueBy) ? $this->uniqueBy : [$this->uniqueBy];
            $uniqueData = [];

            foreach ($uniqueKeys as $key) {
                if (isset($record[$key])) {
                    $uniqueData[$key] = $record[$key];
                }
            }

            if (empty($uniqueData)) {
                $this->command->warn('Skipping record: no unique key found');

                continue;
            }

            // Check if record exists
            $exists = $modelClass::where($uniqueData)->exists();

            // Use updateOrCreate for upsert
            $modelClass::updateOrCreate($uniqueData, $record);

            if ($exists) {
                $updated++;
            } else {
                $created++;
            }
        }

        $this->command->info("Imported {$created} new record(s), updated {$updated} existing record(s)");
    }

    /**
     * Get the storage path for the JSON file.
     */
    protected function getStoragePath(string $filename): string
    {
        $basePath = config('oi-seeds.storage_path', 'app/private/seeders');

        return storage_path("{$basePath}/{$filename}");
    }

    /**
     * Get exportable attributes for a model instance.
     * Override this method in your seeder to customize exported attributes.
     */
    protected function getExportableAttributes(mixed $model): array
    {
        return $model->toArray();
    }

    /**
     * Get the dependencies for this seeder.
     */
    public function getDependencies(): array
    {
        return property_exists($this, 'dependencies') ? $this->dependencies : [];
    }

    /**
     * Check if this seeder is exportable (has the required properties).
     */
    public static function isExportable(): bool
    {
        return property_exists(static::class, 'jsonFilename')
            && property_exists(static::class, 'modelClass');
    }
}
