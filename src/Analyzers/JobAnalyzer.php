<?php

namespace LaraViz\Analyzers;

use ReflectionClass;
use Symfony\Component\Finder\Finder;

class JobAnalyzer
{
    public function analyze(): array
    {
        $jobs = $this->discoverJobs();

        return [
            'total'   => count($jobs),
            'jobs'    => $jobs,
            'summary' => $this->buildSummary($jobs),
        ];
    }

    protected function discoverJobs(): array
    {
        $scanPaths = config('laraviz.scan_paths', [app_path()]);
        $jobs = [];

        foreach ($scanPaths as $path) {
            $jobsDirs = [
                rtrim($path, '/') . '/Jobs',
                rtrim($path, '/'),
            ];

            foreach ($jobsDirs as $dir) {
                if (! is_dir($dir)) {
                    continue;
                }

                $finder = Finder::create()->files()->name('*.php')->in($dir)->depth('< 3');

                foreach ($finder as $file) {
                    $fqn = $this->fqnFromFile($file->getRealPath());
                    if ($fqn && $this->isJob($fqn)) {
                        try {
                            $jobs[] = $this->analyzeJob($fqn, $file->getRealPath());
                        } catch (\Throwable) {
                            // skip
                        }
                    }
                }
            }
        }

        return array_unique($jobs, SORT_REGULAR);
    }

    protected function isJob(string $fqn): bool
    {
        try {
            if (! class_exists($fqn)) {
                return false;
            }
            return is_subclass_of($fqn, \Illuminate\Contracts\Queue\ShouldQueue::class)
                || $this->hasDispatchableMethod($fqn);
        } catch (\Throwable) {
            return false;
        }
    }

    protected function hasDispatchableMethod(string $fqn): bool
    {
        try {
            $ref = new ReflectionClass($fqn);
            return $ref->hasMethod('handle')
                && ! $ref->isAbstract()
                && $ref->hasMethod('dispatch');
        } catch (\Throwable) {
            return false;
        }
    }

    protected function analyzeJob(string $fqn, string $filePath): array
    {
        $reflection = new ReflectionClass($fqn);

        return [
            'class'       => $fqn,
            'short_name'  => class_basename($fqn),
            'is_queued'   => is_subclass_of($fqn, \Illuminate\Contracts\Queue\ShouldQueue::class),
            'is_unique'   => is_subclass_of($fqn, \Illuminate\Contracts\Queue\ShouldBeUnique::class),
            'is_encrypted'=> $this->usesEncryption($reflection),
            'traits'      => $this->getTraits($reflection),
            'queue'       => $this->getQueueProperty($reflection),
            'connection'  => $this->getConnectionProperty($reflection),
            'tries'       => $this->getIntProperty($reflection, 'tries'),
            'timeout'     => $this->getIntProperty($reflection, 'timeout'),
            'max_exceptions' => $this->getIntProperty($reflection, 'maxExceptions'),
            'chained'     => $this->isChained($reflection),
            'file'        => $this->getRelativePath($filePath),
        ];
    }

    protected function usesEncryption(ReflectionClass $reflection): bool
    {
        foreach ($reflection->getInterfaces() as $interface) {
            if (str_contains($interface->getName(), 'ShouldBeEncrypted')) {
                return true;
            }
        }
        return false;
    }

    protected function getTraits(ReflectionClass $reflection): array
    {
        return array_map(
            fn ($t) => class_basename($t->getName()),
            $reflection->getTraits()
        );
    }

    protected function getQueueProperty(ReflectionClass $reflection): ?string
    {
        try {
            $instance = $reflection->newInstanceWithoutConstructor();
            return property_exists($instance, 'queue') ? $instance->queue : null;
        } catch (\Throwable) {
            return null;
        }
    }

    protected function getConnectionProperty(ReflectionClass $reflection): ?string
    {
        try {
            $instance = $reflection->newInstanceWithoutConstructor();
            return property_exists($instance, 'connection') ? $instance->connection : null;
        } catch (\Throwable) {
            return null;
        }
    }

    protected function getIntProperty(ReflectionClass $reflection, string $property): ?int
    {
        try {
            $instance = $reflection->newInstanceWithoutConstructor();
            return property_exists($instance, $property) ? (int) $instance->{$property} : null;
        } catch (\Throwable) {
            return null;
        }
    }

    protected function isChained(ReflectionClass $reflection): bool
    {
        foreach ($reflection->getTraits() as $trait) {
            if (str_contains($trait->getName(), 'Queueable')) {
                return true;
            }
        }
        return false;
    }

    protected function buildSummary(array $jobs): array
    {
        return [
            'total'       => count($jobs),
            'queued'      => count(array_filter($jobs, fn ($j) => $j['is_queued'])),
            'synchronous' => count(array_filter($jobs, fn ($j) => ! $j['is_queued'])),
            'unique'      => count(array_filter($jobs, fn ($j) => $j['is_unique'])),
            'queues'      => array_values(array_unique(array_filter(array_column($jobs, 'queue')))),
        ];
    }

    protected function fqnFromFile(string $path): ?string
    {
        $contents = file_get_contents($path);
        preg_match('/^namespace\s+([^;]+);/m', $contents, $nsMatch);
        preg_match('/^class\s+(\w+)/m', $contents, $classMatch);

        if (! isset($classMatch[1])) {
            return null;
        }

        $namespace = $nsMatch[1] ?? '';
        return $namespace ? "{$namespace}\\{$classMatch[1]}" : $classMatch[1];
    }

    protected function getRelativePath(string $path): string
    {
        return str_replace(base_path() . '/', '', $path);
    }
}
