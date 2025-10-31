<?php

namespace OiLab\LaravelSeeds\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use OiLab\LaravelSeeds\Traits\ExportableSeeder;
use RuntimeException;
use SplFileInfo;

class ExportSeedersCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'seed:export
                            {--seeder= : Export specific seeder by class name}
                            {--with-relations : Include relations in export}';

    /**
     * The console command description.
     */
    protected $description = 'Export data from seeders to JSON files';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Exporting seeders data...');
        $this->newLine();

        $specificSeeder = $this->option('seeder');
        $withRelations = $this->option('with-relations');

        // Get all exportable seeders
        $seeders = $this->getExportableSeeders($specificSeeder);

        if (empty($seeders)) {
            $this->warn('No exportable seeders found');

            return Command::FAILURE;
        }

        // Resolve dependencies
        $orderedSeeders = $this->resolveDependencies($seeders);

        $this->info('Found '.count($orderedSeeders).' seeder(s) to export');
        $this->newLine();

        $totalExported = 0;

        foreach ($orderedSeeders as $seederClass) {
            $seeder = new $seederClass;
            $seeder->setCommand($this);

            $this->line("Exporting <fg=cyan>{$this->getSeederName($seederClass)}</>");

            try {
                $count = $seeder->exportData($withRelations);
                $totalExported += $count;
            } catch (Exception $e) {
                $this->error("  Failed: {$e->getMessage()}");

                continue;
            }

            $this->newLine();
        }

        $this->components->info("Successfully exported {$totalExported} total record(s)");

        return Command::SUCCESS;
    }

    /**
     * Get all exportable seeders or a specific one.
     */
    protected function getExportableSeeders(?string $specificSeeder): array
    {
        $seedersPath = database_path('seeders');
        $files = File::allFiles($seedersPath);
        $exportableSeeders = [];

        foreach ($files as $file) {
            $className = $this->getClassNameFromFile($file);

            if (! $className) {
                continue;
            }

            // Skip if specific seeder requested and this isn't it
            if ($specificSeeder && $this->getSeederName($className) !== $specificSeeder) {
                continue;
            }

            // Check if class uses ExportableSeeder trait
            if (! $this->usesExportableTrait($className)) {
                continue;
            }

            $exportableSeeders[] = $className;
        }

        return $exportableSeeders;
    }

    /**
     * Get class name from file.
     */
    protected function getClassNameFromFile(SplFileInfo $file): ?string
    {
        $relativePath = str_replace(database_path('seeders').'/', '', $file->getPathname());
        $relativePath = str_replace('.php', '', $relativePath);
        $className = 'Database\\Seeders\\'.str_replace('/', '\\', $relativePath);

        if (! class_exists($className)) {
            return null;
        }

        return $className;
    }

    /**
     * Get simple seeder name from full class name.
     */
    protected function getSeederName(string $className): string
    {
        $parts = explode('\\', $className);

        return end($parts);
    }

    /**
     * Check if class uses ExportableSeeder trait.
     */
    protected function usesExportableTrait(string $className): bool
    {
        $traits = class_uses_recursive($className);

        return in_array(ExportableSeeder::class, $traits);
    }

    /**
     * Resolve seeder dependencies using topological sort.
     */
    protected function resolveDependencies(array $seeders): array
    {
        $resolved = [];
        $unresolved = [];

        foreach ($seeders as $seeder) {
            $this->resolveDependency($seeder, $seeders, $resolved, $unresolved);
        }

        return $resolved;
    }

    /**
     * Recursively resolve a single seeder's dependencies.
     */
    protected function resolveDependency(string $seeder, array $allSeeders, array &$resolved, array &$unresolved): void
    {
        // Skip if already resolved
        if (in_array($seeder, $resolved)) {
            return;
        }

        $unresolved[] = $seeder;

        $instance = new $seeder;
        $dependencies = $instance->getDependencies();

        foreach ($dependencies as $dependency) {
            // Only process dependency if it's in our exportable list
            if (! in_array($dependency, $allSeeders)) {
                continue;
            }

            if (! in_array($dependency, $resolved)) {
                if (in_array($dependency, $unresolved)) {
                    throw new RuntimeException("Circular dependency detected: {$seeder} -> {$dependency}");
                }

                $this->resolveDependency($dependency, $allSeeders, $resolved, $unresolved);
            }
        }

        $resolved[] = $seeder;
        $unresolved = array_diff($unresolved, [$seeder]);
    }
}
