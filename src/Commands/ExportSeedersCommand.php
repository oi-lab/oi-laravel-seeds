<?php

namespace OiLab\OiLaravelSeeds\Commands;

use Exception;
use Illuminate\Console\Command;
use OiLab\OiLaravelSeeds\Traits\DiscoversSeeders;

class ExportSeedersCommand extends Command
{
    use DiscoversSeeders;

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
}
