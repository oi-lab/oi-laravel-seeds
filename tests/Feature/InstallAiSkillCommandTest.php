<?php

use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->base = base_path();
});

it('installs skill files and a CLAUDE.md rules section', function () {
    File::delete(base_path('CLAUDE.md'));
    File::deleteDirectory(base_path('.claude'));
    File::deleteDirectory(base_path('.junie'));

    $this->artisan('oi-seeds:install-ai-skill')->assertSuccessful();

    expect(File::exists(base_path('.claude/skills/oilab-laravel-seeds/SKILL.md')))->toBeTrue()
        ->and(File::exists(base_path('.junie/skills/oilab-laravel-seeds/SKILL.md')))->toBeTrue()
        ->and(File::get(base_path('CLAUDE.md')))->toContain('=== oi-lab/oi-laravel-seeds rules ===');
});

it('does not duplicate the rules section on re-run', function () {
    File::delete(base_path('CLAUDE.md'));

    $this->artisan('oi-seeds:install-ai-skill')->assertSuccessful();
    $this->artisan('oi-seeds:install-ai-skill')->assertSuccessful();

    $occurrences = substr_count(
        File::get(base_path('CLAUDE.md')),
        '=== oi-lab/oi-laravel-seeds rules ==='
    );

    expect($occurrences)->toBe(1);
});

afterEach(function () {
    File::delete(base_path('CLAUDE.md'));
    File::deleteDirectory(base_path('.claude'));
    File::deleteDirectory(base_path('.junie'));
});
