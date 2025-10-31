<?php

use Illuminate\Support\Facades\File;
use OiLab\LaravelSeeds\Tests\Fixtures\TestGroup;
use OiLab\LaravelSeeds\Tests\Fixtures\TestUser;

beforeEach(function () {
    $this->storagePath = storage_path('app/testing/seeders');

    // Copy test seeders to database/seeders for discovery
    File::ensureDirectoryExists(database_path('seeders'));
    File::copy(
        __DIR__.'/../Fixtures/TestGroupSeeder.php',
        database_path('seeders/TestGroupSeeder.php')
    );
    File::copy(
        __DIR__.'/../Fixtures/TestUserSeeder.php',
        database_path('seeders/TestUserSeeder.php')
    );
});

afterEach(function () {
    // Clean up copied seeders
    File::delete(database_path('seeders/TestGroupSeeder.php'));
    File::delete(database_path('seeders/TestUserSeeder.php'));
});

it('can import all seeders', function () {
    // Create JSON files
    $groupsData = [
        ['name' => 'Admin', 'description' => 'Administrators'],
        ['name' => 'User', 'description' => 'Regular users'],
    ];

    $usersData = [
        ['name' => 'John Doe', 'email' => 'john@example.com'],
        ['name' => 'Jane Smith', 'email' => 'jane@example.com'],
    ];

    File::ensureDirectoryExists($this->storagePath);
    File::put("{$this->storagePath}/test_groups.json", json_encode($groupsData));
    File::put("{$this->storagePath}/test_users.json", json_encode($usersData));

    // Run import command
    $this->artisan('seed:import')
        ->expectsOutput('Importing seeders data...')
        ->assertSuccessful();

    // Assert data imported
    expect(TestGroup::count())->toBe(2);
    expect(TestUser::count())->toBe(2);
    expect(TestGroup::where('name', 'Admin')->exists())->toBeTrue();
    expect(TestUser::where('email', 'john@example.com')->exists())->toBeTrue();
});

it('can import specific seeder', function () {
    // Create JSON files
    $groupsData = [
        ['name' => 'Admin', 'description' => 'Administrators'],
    ];

    $usersData = [
        ['name' => 'John Doe', 'email' => 'john@example.com'],
    ];

    File::ensureDirectoryExists($this->storagePath);
    File::put("{$this->storagePath}/test_groups.json", json_encode($groupsData));
    File::put("{$this->storagePath}/test_users.json", json_encode($usersData));

    // Run import command for specific seeder
    $this->artisan('seed:import', ['--seeder' => 'TestGroupSeeder'])
        ->assertSuccessful();

    // Assert only groups imported
    expect(TestGroup::count())->toBe(1);
    expect(TestUser::count())->toBe(0);
});

it('handles missing json file gracefully', function () {
    $this->artisan('seed:import', ['--seeder' => 'TestGroupSeeder'])
        ->expectsOutputToContain('File not found')
        ->assertSuccessful();

    expect(TestGroup::count())->toBe(0);
});

it('handles invalid json gracefully', function () {
    // Create invalid JSON file
    File::ensureDirectoryExists($this->storagePath);
    File::put("{$this->storagePath}/test_groups.json", 'invalid json{]');

    $this->artisan('seed:import', ['--seeder' => 'TestGroupSeeder'])
        ->expectsOutputToContain('Invalid JSON')
        ->assertSuccessful();

    expect(TestGroup::count())->toBe(0);
});

it('upserts existing records', function () {
    // Create existing data
    TestGroup::create(['name' => 'Admin', 'description' => 'Old description']);

    // Create JSON file with updated data
    $groupsData = [
        ['name' => 'Admin', 'description' => 'New description'],
        ['name' => 'User', 'description' => 'Regular users'],
    ];

    File::ensureDirectoryExists($this->storagePath);
    File::put("{$this->storagePath}/test_groups.json", json_encode($groupsData));

    // Run import command
    $this->artisan('seed:import', ['--seeder' => 'TestGroupSeeder'])
        ->assertSuccessful();

    // Assert data upserted
    expect(TestGroup::count())->toBe(2);
    expect(TestGroup::where('name', 'Admin')->first()->description)->toBe('New description');
});

it('shows import statistics', function () {
    // Create JSON file
    $groupsData = [
        ['name' => 'Admin', 'description' => 'Administrators'],
        ['name' => 'User', 'description' => 'Regular users'],
    ];

    File::ensureDirectoryExists($this->storagePath);
    File::put("{$this->storagePath}/test_groups.json", json_encode($groupsData));

    $this->artisan('seed:import', ['--seeder' => 'TestGroupSeeder'])
        ->expectsOutputToContain('Imported 2 new record(s), updated 0 existing record(s)')
        ->assertSuccessful();
});

it('handles no exportable seeders gracefully', function () {
    // Remove test seeders
    File::delete(database_path('seeders/TestGroupSeeder.php'));
    File::delete(database_path('seeders/TestUserSeeder.php'));

    $this->artisan('seed:import')
        ->expectsOutput('No exportable seeders found')
        ->assertFailed();
});

it('continues on error and imports other seeders', function () {
    // Create only groups JSON (users will have error if missing)
    $groupsData = [
        ['name' => 'Admin', 'description' => 'Administrators'],
    ];

    File::ensureDirectoryExists($this->storagePath);
    File::put("{$this->storagePath}/test_groups.json", json_encode($groupsData));

    $this->artisan('seed:import')
        ->assertSuccessful();

    // Groups should be imported
    expect(TestGroup::count())->toBe(1);
});
