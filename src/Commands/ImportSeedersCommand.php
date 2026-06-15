<?php

namespace OiLab\OiLaravelSeeds\Commands;

use Exception;
use Illuminate\Console\Command;
use OiLab\OiLaravelSeeds\Traits\DiscoversSeeders;

class ImportSeedersCommand extends Command
{
    use DiscoversSeeders;

    /**
     * The name and signature of the console command.
     */
    protected $signature = 'seed:import
                            {--seeder= : Import specific seeder by class name}';

    /**
     * The console command description.
     */
    protected $description = 'Import data from JSON files to database using seeders';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Importing seeders data...');
        $this->newLine();

        $specificSeeder = $this->option('seeder');

        // Get all exportable seeders
        $seeders = $this->getExportableSeeders($specificSeeder);

        if (empty($seeders)) {
            $this->warn('No exportable seeders found');

            return Command::FAILURE;
        }

        // Resolve dependencies
        $orderedSeeders = $this->resolveDependencies($seeders);

        $this->info('Found '.count($orderedSeeders).' seeder(s) to import');
        $this->newLine();

        foreach ($orderedSeeders as $seederClass) {
            $seeder = new $seederClass;
            $seeder->setCommand($this);

            $this->line("Importing <fg=cyan>{$this->getSeederName($seederClass)}</>");

            try {
                $seeder->importData();
            } catch (Exception $e) {
                $this->error("  Failed: {$e->getMessage()}");

                continue;
            }

            $this->newLine();
        }

        $this->components->info('Successfully imported all data');

        return Command::SUCCESS;
    }
}
