<?php

namespace OiLab\OiLaravelSeeds;

use Illuminate\Support\ServiceProvider;
use OiLab\OiLaravelSeeds\Commands\ExportSeedersCommand;
use OiLab\OiLaravelSeeds\Commands\ImportSeedersCommand;
use OiLab\OiLaravelSeeds\Commands\MakeExportableSeederCommand;

class OiLaravelSeedsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/oi-laravel-seeds.php',
            'oi-seeds'
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/oi-laravel-seeds.php' => config_path('oi-seeds.php'),
        ], 'oi-seeds-config');

        // Publish stubs
        $this->publishes([
            __DIR__.'/../stubs' => base_path('stubs/oi-seeds'),
        ], 'oi-seeds-stubs');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                ExportSeedersCommand::class,
                ImportSeedersCommand::class,
                MakeExportableSeederCommand::class,
            ]);
        }
    }
}
