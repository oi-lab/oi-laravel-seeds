<?php

use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->seedersPath = database_path('seeders');
    File::ensureDirectoryExists($this->seedersPath);
});

afterEach(function () {
    // Clean up created seeders
    $files = File::glob("{$this->seedersPath}/*Seeder.php");
    foreach ($files as $file) {
        if (basename($file) !== 'DatabaseSeeder.php') {
            File::delete($file);
        }
    }
});

it('can generate an exportable seeder', function () {
    $this->artisan('make:exportable-seeder', ['name' => 'ProductSeeder'])
        ->assertSuccessful();

    $file = database_path('seeders/ProductSeeder.php');
    expect(File::exists($file))->toBeTrue();

    $content = File::get($file);
    expect($content)->toContain('class ProductSeeder extends Seeder');
    expect($content)->toContain('use ExportableSeeder;');
    expect($content)->toContain('protected string $jsonFilename');
    expect($content)->toContain('protected string $modelClass');
    expect($content)->toContain('protected string $uniqueBy');
});

it('can generate seeder with model option', function () {
    $this->artisan('make:exportable-seeder', [
        'name' => 'ProductSeeder',
        '--model' => 'Product',
    ])
        ->assertSuccessful();

    $content = File::get(database_path('seeders/ProductSeeder.php'));
    expect($content)->toContain('Product::class');
    expect($content)->toContain("'products.json'");
});

it('can generate seeder with custom unique-by', function () {
    $this->artisan('make:exportable-seeder', [
        'name' => 'ProductSeeder',
        '--unique-by' => 'sku',
    ])
        ->assertSuccessful();

    $content = File::get(database_path('seeders/ProductSeeder.php'));
    expect($content)->toContain("'sku'");
});

it('can generate seeder with custom json filename', function () {
    $this->artisan('make:exportable-seeder', [
        'name' => 'ProductSeeder',
        '--json-filename' => 'custom_products.json',
    ])
        ->assertSuccessful();

    $content = File::get(database_path('seeders/ProductSeeder.php'));
    expect($content)->toContain("'custom_products.json'");
});

it('generates seeder with all options', function () {
    $this->artisan('make:exportable-seeder', [
        'name' => 'ProductSeeder',
        '--model' => 'Product',
        '--unique-by' => 'sku',
        '--json-filename' => 'custom_products.json',
    ])
        ->assertSuccessful();

    $content = File::get(database_path('seeders/ProductSeeder.php'));
    expect($content)->toContain('Product::class');
    expect($content)->toContain("'sku'");
    expect($content)->toContain("'custom_products.json'");
});

it('includes required methods in generated seeder', function () {
    $this->artisan('make:exportable-seeder', ['name' => 'ProductSeeder'])
        ->assertSuccessful();

    $content = File::get(database_path('seeders/ProductSeeder.php'));
    expect($content)->toContain('public function run(): void');
    expect($content)->toContain('$this->importData()');
    expect($content)->toContain('protected function getExportableAttributes(mixed $model): array');
    expect($content)->toContain('protected array $dependencies = []');
    expect($content)->toContain('protected array $exportRelations = []');
});

it('uses correct namespace', function () {
    $this->artisan('make:exportable-seeder', ['name' => 'ProductSeeder'])
        ->assertSuccessful();

    $content = File::get(database_path('seeders/ProductSeeder.php'));
    expect($content)->toContain('namespace Database\Seeders;');
    expect($content)->toContain('use Illuminate\Database\Seeder;');
    expect($content)->toContain('use OiLab\OiLaravelSeeds\Traits\ExportableSeeder;');
});

it('uses default unique-by when not specified', function () {
    $this->artisan('make:exportable-seeder', ['name' => 'ProductSeeder'])
        ->assertSuccessful();

    $content = File::get(database_path('seeders/ProductSeeder.php'));
    expect($content)->toContain("'id'");
});

it('generates valid php syntax', function () {
    $this->artisan('make:exportable-seeder', [
        'name' => 'ProductSeeder',
        '--model' => 'Product',
    ])
        ->assertSuccessful();

    $file = database_path('seeders/ProductSeeder.php');

    // Check if file has valid PHP syntax
    $output = shell_exec("php -l {$file} 2>&1");
    expect($output)->toContain('No syntax errors');
});
