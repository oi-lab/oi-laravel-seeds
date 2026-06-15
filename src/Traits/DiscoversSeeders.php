<?php

namespace OiLab\OiLaravelSeeds\Traits;

use Illuminate\Support\Facades\File;
use RuntimeException;
use SplFileInfo;

/**
 * Shared seeder discovery and dependency resolution for the export/import commands.
 */
trait DiscoversSeeders
{
    /**
     * Get all exportable seeders or a specific one.
     *
     * @return array<int, class-string>
     */
    protected function getExportableSeeders(?string $specificSeeder): array
    {
        $seedersPath = database_path('seeders');

        if (! File::isDirectory($seedersPath)) {
            return [];
        }

        $exportableSeeders = [];

        foreach (File::allFiles($seedersPath) as $file) {
            $className = $this->getClassNameFromFile($file);

            if (! $className) {
                continue;
            }

            if ($specificSeeder && $this->getSeederName($className) !== $specificSeeder) {
                continue;
            }

            if (! $this->usesExportableTrait($className)) {
                continue;
            }

            $exportableSeeders[] = $className;
        }

        return $exportableSeeders;
    }

    /**
     * Resolve the fully-qualified class name declared in the given file.
     *
     * @return class-string|null
     */
    protected function getClassNameFromFile(SplFileInfo $file): ?string
    {
        $className = $this->parseClassName((string) file_get_contents($file->getPathname()));

        if (! $className) {
            return null;
        }

        if (! class_exists($className)) {
            require_once $file->getPathname();
        }

        return class_exists($className) ? $className : null;
    }

    /**
     * Parse the fully-qualified class name from PHP source using the tokenizer.
     */
    protected function parseClassName(string $contents): ?string
    {
        $tokens = token_get_all($contents);
        $count = count($tokens);
        $namespace = '';
        $class = null;

        for ($i = 0; $i < $count; $i++) {
            $token = $tokens[$i];

            if (is_array($token) && $token[0] === T_NAMESPACE) {
                for ($j = $i + 1; $j < $count; $j++) {
                    if ($tokens[$j] === ';' || $tokens[$j] === '{') {
                        break;
                    }

                    if (is_array($tokens[$j]) && $tokens[$j][0] !== T_WHITESPACE) {
                        $namespace .= $tokens[$j][1];
                    }
                }
            }

            if (is_array($token) && $token[0] === T_CLASS) {
                $previous = $this->previousMeaningfulToken($tokens, $i);

                if (is_array($previous) && $previous[0] === T_DOUBLE_COLON) {
                    continue;
                }

                for ($j = $i + 1; $j < $count; $j++) {
                    if (is_array($tokens[$j]) && $tokens[$j][0] === T_STRING) {
                        $class = $tokens[$j][1];

                        break;
                    }
                }

                break;
            }
        }

        if (! $class) {
            return null;
        }

        return $namespace !== '' ? $namespace.'\\'.$class : $class;
    }

    /**
     * Get the previous non-whitespace token relative to the given index.
     *
     * @param  array<int, mixed>  $tokens
     */
    protected function previousMeaningfulToken(array $tokens, int $index): mixed
    {
        for ($i = $index - 1; $i >= 0; $i--) {
            if (is_array($tokens[$i]) && $tokens[$i][0] === T_WHITESPACE) {
                continue;
            }

            return $tokens[$i];
        }

        return null;
    }

    /**
     * Get the simple seeder name from a fully-qualified class name.
     */
    protected function getSeederName(string $className): string
    {
        return class_basename($className);
    }

    /**
     * Determine whether the class uses the ExportableSeeder trait.
     */
    protected function usesExportableTrait(string $className): bool
    {
        return in_array(ExportableSeeder::class, class_uses_recursive($className), true);
    }

    /**
     * Resolve seeder dependencies using a topological sort.
     *
     * @param  array<int, class-string>  $seeders
     * @return array<int, class-string>
     */
    protected function resolveDependencies(array $seeders): array
    {
        $resolved = [];
        $unresolved = [];

        foreach ($seeders as $seeder) {
            $this->resolveDependency($seeder, $seeders, $resolved, $unresolved);
        }

        return $resolved;
    }

    /**
     * Recursively resolve a single seeder's dependencies.
     *
     * @param  array<int, class-string>  $allSeeders
     * @param  array<int, class-string>  $resolved
     * @param  array<int, class-string>  $unresolved
     */
    protected function resolveDependency(string $seeder, array $allSeeders, array &$resolved, array &$unresolved): void
    {
        if (in_array($seeder, $resolved, true)) {
            return;
        }

        $unresolved[] = $seeder;

        $dependencies = (new $seeder)->getDependencies();

        foreach ($dependencies as $dependency) {
            if (! in_array($dependency, $allSeeders, true)) {
                continue;
            }

            if (! in_array($dependency, $resolved, true)) {
                if (in_array($dependency, $unresolved, true)) {
                    throw new RuntimeException("Circular dependency detected: {$seeder} -> {$dependency}");
                }

                $this->resolveDependency($dependency, $allSeeders, $resolved, $unresolved);
            }
        }

        $resolved[] = $seeder;
        $unresolved = array_diff($unresolved, [$seeder]);
    }
}
