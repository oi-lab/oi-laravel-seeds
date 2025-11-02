<?php

use Illuminate\Support\Facades\File;
use OiLab\OiLaravelSeeds\Tests\Fixtures\TestGroup;
use OiLab\OiLaravelSeeds\Tests\Fixtures\TestPost;
use OiLab\OiLaravelSeeds\Tests\Fixtures\TestUser;

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

it('can export all seeders', function () {
    // Create test data
    TestGroup::create(['name' => 'Admin', 'description' => 'Administrators']);
    TestGroup::create(['name' => 'User', 'description' => 'Regular users']);

    TestUser::create(['name' => 'John Doe', 'email' => 'john@example.com']);

    // Run export command
    $this->artisan('seed:export')
        ->expectsOutput('Exporting seeders data...')
        ->assertSuccessful();

    // Assert files created
    expect(File::exists("{$this->storagePath}/test_groups.json"))->toBeTrue();
    expect(File::exists("{$this->storagePath}/test_users.json"))->toBeTrue();

    // Verify data
    $groupsJson = json_decode(File::get("{$this->storagePath}/test_groups.json"), true);
    expect($groupsJson)->toHaveCount(2);

    $usersJson = json_decode(File::get("{$this->storagePath}/test_users.json"), true);
    expect($usersJson)->toHaveCount(1);
});

it('can export specific seeder', function () {
    // Create test data
    TestGroup::create(['name' => 'Admin', 'description' => 'Administrators']);
    TestUser::create(['name' => 'John Doe', 'email' => 'john@example.com']);

    // Run export command for specific seeder
    $this->artisan('seed:export', ['--seeder' => 'TestGroupSeeder'])
        ->assertSuccessful();

    // Assert only groups file created
    expect(File::exists("{$this->storagePath}/test_groups.json"))->toBeTrue();
    expect(File::exists("{$this->storagePath}/test_users.json"))->toBeFalse();
});

it('can export with relations', function () {
    // Create test data with relations
    $user = TestUser::create(['name' => 'John Doe', 'email' => 'john@example.com']);
    TestPost::create(['user_id' => $user->id, 'title' => 'Post 1', 'content' => 'Content 1']);
    TestPost::create(['user_id' => $user->id, 'title' => 'Post 2', 'content' => 'Content 2']);

    // Run export command with relations
    $this->artisan('seed:export', ['--with-relations' => true])
        ->assertSuccessful();

    // Verify relations exported
    $usersJson = json_decode(File::get("{$this->storagePath}/test_users.json"), true);
    expect($usersJson[0]['posts'])->toHaveCount(2);
});

it('handles no exportable seeders gracefully', function () {
    // Remove test seeders
    File::delete(database_path('seeders/TestGroupSeeder.php'));
    File::delete(database_path('seeders/TestUserSeeder.php'));

    $this->artisan('seed:export')
        ->expectsOutput('No exportable seeders found')
        ->assertFailed();
});

it('continues on error and exports other seeders', function () {
    // Create only groups data (users seeder will fail to export if empty)
    TestGroup::create(['name' => 'Admin', 'description' => 'Administrators']);

    $this->artisan('seed:export')
        ->assertSuccessful();

    // Groups should be exported
    expect(File::exists("{$this->storagePath}/test_groups.json"))->toBeTrue();
});

it('shows export count', function () {
    TestGroup::create(['name' => 'Admin', 'description' => 'Administrators']);
    TestGroup::create(['name' => 'User', 'description' => 'Regular users']);

    $this->artisan('seed:export', ['--seeder' => 'TestGroupSeeder'])
        ->expectsOutputToContain('Exported 2 record(s)')
        ->assertSuccessful();
});
