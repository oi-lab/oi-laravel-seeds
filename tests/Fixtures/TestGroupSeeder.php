<?php

namespace OiLab\OiLaravelSeeds\Tests\Fixtures;

use Illuminate\Database\Seeder;
use OiLab\OiLaravelSeeds\Traits\ExportableSeeder;

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
