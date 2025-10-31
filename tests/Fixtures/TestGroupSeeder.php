<?php

namespace OiLab\LaravelSeeds\Tests\Fixtures;

use Illuminate\Database\Seeder;
use OiLab\LaravelSeeds\Traits\ExportableSeeder;

class TestGroupSeeder extends Seeder
{
    use ExportableSeeder;

    protected string $jsonFilename = 'test_groups.json';

    protected string $modelClass = TestGroup::class;

    protected array $dependencies = [];

    protected string $uniqueBy = 'name';

    public function run(): void
    {
        $this->importData();
    }
}
