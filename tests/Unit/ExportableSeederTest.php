<?php

use Illuminate\Support\Facades\File;
use OiLab\OiLaravelSeeds\Tests\Fixtures\TestGroup;
use OiLab\OiLaravelSeeds\Tests\Fixtures\TestGroupSeeder;
use OiLab\OiLaravelSeeds\Tests\Fixtures\TestPost;
use OiLab\OiLaravelSeeds\Tests\Fixtures\TestUser;
use OiLab\OiLaravelSeeds\Tests\Fixtures\TestUserSeeder;

beforeEach(function () {
    $this->storagePath = storage_path('app/testing/seeders');
});

it('can export data to json file', function () {
    // Create test data
    TestGroup::create(['name' => 'Admin', 'description' => 'Administrators']);
    TestGroup::create(['name' => 'User', 'description' => 'Regular users']);

    // Export data
    $seeder = new TestGroupSeeder;
    $seeder->setCommand($this->artisan('list'));
    $count = $seeder->exportData();

    // Assert
    expect($count)->toBe(2);
    expect(File::exists("{$this->storagePath}/test_groups.json"))->toBeTrue();

    $json = json_decode(File::get("{$this->storagePath}/test_groups.json"), true);
    expect($json)->toHaveCount(2);
    expect($json[0]['name'])->toBe('Admin');
    expect($json[1]['name'])->toBe('User');
});

it('can import data from json file', function () {
    // Create JSON file
    $data = [
        ['name' => 'Admin', 'description' => 'Administrators'],
        ['name' => 'User', 'description' => 'Regular users'],
    ];

    File::ensureDirectoryExists($this->storagePath);
    File::put("{$this->storagePath}/test_groups.json", json_encode($data));

    // Import data
    $seeder = new TestGroupSeeder;
    $seeder->setCommand($this->artisan('list'));
    $seeder->importData();

    // Assert
    expect(TestGroup::count())->toBe(2);
    expect(TestGroup::where('name', 'Admin')->exists())->toBeTrue();
    expect(TestGroup::where('name', 'User')->exists())->toBeTrue();
});

it('can upsert data when importing', function () {
    // Create existing data
    TestGroup::create(['name' => 'Admin', 'description' => 'Old description']);

    // Create JSON file with updated data
    $data = [
        ['name' => 'Admin', 'description' => 'Updated description'],
        ['name' => 'User', 'description' => 'Regular users'],
    ];

    File::ensureDirectoryExists($this->storagePath);
    File::put("{$this->storagePath}/test_groups.json", json_encode($data));

    // Import data
    $seeder = new TestGroupSeeder;
    $seeder->setCommand($this->artisan('list'));
    $seeder->importData();

    // Assert
    expect(TestGroup::count())->toBe(2);
    expect(TestGroup::where('name', 'Admin')->first()->description)->toBe('Updated description');
});

it('can export with relations', function () {
    // Create test data
    $user = TestUser::create(['name' => 'John Doe', 'email' => 'john@example.com']);
    TestPost::create(['user_id' => $user->id, 'title' => 'Post 1', 'content' => 'Content 1']);
    TestPost::create(['user_id' => $user->id, 'title' => 'Post 2', 'content' => 'Content 2']);

    // Export data with relations
    $seeder = new TestUserSeeder;
    $seeder->setCommand($this->artisan('list'));
    $count = $seeder->exportData(true);

    // Assert
    expect($count)->toBe(1);
    expect(File::exists("{$this->storagePath}/test_users.json"))->toBeTrue();

    $json = json_decode(File::get("{$this->storagePath}/test_users.json"), true);
    expect($json)->toHaveCount(1);
    expect($json[0]['posts'])->toHaveCount(2);
});

it('returns dependencies', function () {
    $seeder = new TestGroupSeeder;
    $dependencies = $seeder->getDependencies();

    expect($dependencies)->toBeArray();
    expect($dependencies)->toBeEmpty();
});

it('checks if seeder is exportable', function () {
    expect(TestGroupSeeder::isExportable())->toBeTrue();
});

it('handles missing json file gracefully', function () {
    $seeder = new TestGroupSeeder;
    $seeder->setCommand($this->artisan('list'));
    $seeder->importData();

    // Should not throw exception
    expect(TestGroup::count())->toBe(0);
});

it('handles invalid json gracefully', function () {
    // Create invalid JSON file
    File::ensureDirectoryExists($this->storagePath);
    File::put("{$this->storagePath}/test_groups.json", 'invalid json{]');

    $seeder = new TestGroupSeeder;
    $seeder->setCommand($this->artisan('list'));
    $seeder->importData();

    // Should not throw exception
    expect(TestGroup::count())->toBe(0);
});

it('skips records without unique keys', function () {
    // Create JSON file with missing unique key
    $data = [
        ['description' => 'No name provided'],
        ['name' => 'Valid', 'description' => 'Has name'],
    ];

    File::ensureDirectoryExists($this->storagePath);
    File::put("{$this->storagePath}/test_groups.json", json_encode($data));

    $seeder = new TestGroupSeeder;
    $seeder->setCommand($this->artisan('list'));
    $seeder->importData();

    // Assert only valid record imported
    expect(TestGroup::count())->toBe(1);
    expect(TestGroup::first()->name)->toBe('Valid');
});

it('can customize exportable attributes', function () {
    // Create test data
    TestGroup::create(['name' => 'Admin', 'description' => 'Administrators']);

    // Create custom seeder
    $seeder = new class extends TestGroupSeeder
    {
        protected function getExportableAttributes(mixed $model): array
        {
            $attributes = $model->toArray();
            unset($attributes['description']);

            return $attributes;
        }
    };

    $seeder->setCommand($this->artisan('list'));
    $seeder->exportData();

    // Assert description not exported
    $json = json_decode(File::get("{$this->storagePath}/test_groups.json"), true);
    expect($json[0])->not->toHaveKey('description');
    expect($json[0])->toHaveKey('name');
});
