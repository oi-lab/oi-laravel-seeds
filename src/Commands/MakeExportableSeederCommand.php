<?php

namespace OiLab\OiLaravelSeeds\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'make:exportable-seeder')]
class MakeExportableSeederCommand extends GeneratorCommand
{
    /**
     * The console command name.
     */
    protected $name = 'make:exportable-seeder';

    /**
     * The console command description.
     */
    protected $description = 'Create a new exportable seeder class';

    /**
     * The type of class being generated.
     */
    protected $type = 'Seeder';

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        return __DIR__.'/../../stubs/exportable-seeder.stub';
    }

    /**
     * Get the default namespace for the class.
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return 'Database\\Seeders';
    }

    /**
     * Get the destination class path.
     */
    protected function getPath($name): string
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        return database_path('seeders').str_replace('\\', '/', $name).'.php';
    }

    /**
     * Build the class with the given name.
     */
    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);

        $model = $this->option('model');
        $uniqueBy = $this->option('unique-by') ?: 'id';
        $jsonFilename = $this->option('json-filename') ?: Str::snake(Str::plural(class_basename($model ?: $name))).'.json';

        if ($model) {
            $stub = $this->replaceModel($stub, $model);
        } else {
            $stub = str_replace('{{ modelClass }}', 'App\\Models\\YourModel::class', $stub);
            $stub = str_replace('{{modelClass}}', 'App\\Models\\YourModel::class', $stub);
        }

        $stub = str_replace('{{ uniqueBy }}', "'{$uniqueBy}'", $stub);
        $stub = str_replace('{{uniqueBy}}', "'{$uniqueBy}'", $stub);

        $stub = str_replace('{{ jsonFilename }}', "'{$jsonFilename}'", $stub);
        $stub = str_replace('{{jsonFilename}}', "'{$jsonFilename}'", $stub);

        return $stub;
    }

    /**
     * Replace the model for the given stub.
     */
    protected function replaceModel(string $stub, string $model): string
    {
        $modelClass = $this->parseModel($model);

        if (! class_exists($modelClass)) {
            if ($this->confirm("Model [{$modelClass}] does not exist. Do you want to generate it?", true)) {
                $this->call('make:model', ['name' => $modelClass]);
            }
        }

        $stub = str_replace('{{ modelClass }}', $modelClass.'::class', $stub);
        $stub = str_replace('{{modelClass}}', $modelClass.'::class', $stub);

        return $stub;
    }

    /**
     * Get the fully-qualified model class name.
     */
    protected function parseModel(string $model): string
    {
        if (preg_match('([^A-Za-z0-9_/\\\\])', $model)) {
            throw new \InvalidArgumentException('Model name contains invalid characters.');
        }

        return $this->qualifyModel($model);
    }

    /**
     * Get the console command options.
     */
    protected function getOptions(): array
    {
        return [
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'The model class to use for this seeder'],
            ['unique-by', 'u', InputOption::VALUE_OPTIONAL, 'The unique column(s) for upsert operations'],
            ['json-filename', 'j', InputOption::VALUE_OPTIONAL, 'The JSON filename for export/import'],
        ];
    }
}
