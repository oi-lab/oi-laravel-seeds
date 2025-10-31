<?php

namespace OiLab\LaravelSeeds\Tests\Fixtures;

use Illuminate\Database\Seeder;
use OiLab\LaravelSeeds\Traits\ExportableSeeder;

class TestUserSeeder extends Seeder
{
    use ExportableSeeder;

    protected string $jsonFilename = 'test_users.json';

    protected string $modelClass = TestUser::class;

    protected array $dependencies = [];

    protected string $uniqueBy = 'email';

    protected array $exportRelations = ['posts'];

    public function run(): void
    {
        $this->importData();
    }
}
