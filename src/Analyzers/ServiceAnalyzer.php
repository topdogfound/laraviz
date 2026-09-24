<?php

namespace LaraViz\Analyzers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use ReflectionClass;

class ServiceAnalyzer
{
    public function __construct(protected Application $app) {}

    public function analyze(): array
    {
        return [
            'providers'  => $this->analyzeProviders(),
            'bindings'   => $this->analyzeBindings(),
            'facades'    => $this->analyzeFacades(),
            'singletons' => $this->analyzeSingletons(),
        ];
    }

    protected function analyzeProviders(): array
    {
        $providers = [];

        foreach ($this->app->getLoadedProviders() as $provider => $loaded) {
            if (! $loaded) {
                continue;
            }

            try {
                $reflection = new ReflectionClass($provider);
                $providers[] = [
                    'class'      => $provider,
                    'short_name' => class_basename($provider),
                    'is_deferred'=> $this->isDeferredProvider($provider),
                    'is_core'    => $this->isCoreProvider($provider),
                    'file'       => $this->getRelativePath($reflection->getFileName()),
                ];
            } catch (\Throwable) {
                $providers[] = [
                    'class'       => $provider,
                    'short_name'  => class_basename($provider),
                    'is_deferred' => false,
                    'is_core'     => true,
                    'file'        => null,
                ];
            }
        }

        return $providers;
    }

    protected function isDeferredProvider(string $class): bool
    {
        return is_subclass_of($class, \Illuminate\Contracts\Support\DeferrableProvider::class);
    }

    protected function isCoreProvider(string $class): bool
    {
        return str_starts_with($class, 'Illuminate\\')
            || str_starts_with($class, 'Laravel\\')
            || str_starts_with($class, 'Barryvdh\\')
            || str_starts_with($class, 'LaraViz\\');
    }

    protected function analyzeBindings(): array
    {
        $bindings = [];

        try {
            // Access the container's internal bindings array
            $reflection = new ReflectionClass($this->app);
            $property = $reflection->getProperty('bindings');
            $property->setAccessible(true);
            $rawBindings = $property->getValue($this->app);

            foreach ($rawBindings as $abstract => $binding) {
                $concrete = null;
                if (is_array($binding) && isset($binding['concrete'])) {
                    if ($binding['concrete'] instanceof \Closure) {
                        $concrete = 'Closure';
                    } elseif (is_string($binding['concrete'])) {
                        $concrete = class_basename($binding['concrete']);
                    }
                }

                $bindings[] = [
                    'abstract'  => $abstract,
                    'concrete'  => $concrete,
                    'shared'    => $binding['shared'] ?? false,
                    'is_interface' => interface_exists($abstract),
                    'is_core'   => $this->isCoreBinding($abstract),
                ];
            }
        } catch (\Throwable) {
            // Container reflection failed
        }

        // Sort: user bindings first
        usort($bindings, fn ($a, $b) => $a['is_core'] <=> $b['is_core']);

        return $bindings;
    }

    protected function isCoreBinding(string $abstract): bool
    {
        return str_starts_with($abstract, 'Illuminate\\')
            || str_starts_with($abstract, 'Laravel\\')
            || str_starts_with($abstract, 'laravel.');
    }

    protected function analyzeFacades(): array
    {
        $facades = [];
        $facadeBase = \Illuminate\Support\Facades\Facade::class;

        // Scan known facade namespaces
        foreach (get_declared_classes() as $class) {
            try {
                if (is_subclass_of($class, $facadeBase)) {
                    $facades[] = [
                        'facade'     => class_basename($class),
                        'class'      => $class,
                        'accessor'   => $class::getFacadeAccessor(),
                        'is_core'    => str_starts_with($class, 'Illuminate\\'),
                    ];
                }
            } catch (\Throwable) {
                // Some facades might throw when getFacadeAccessor is called
            }
        }

        return $facades;
    }

    protected function analyzeSingletons(): array
    {
        $singletons = [];

        try {
            $reflection = new ReflectionClass($this->app);
            $property = $reflection->getProperty('instances');
            $property->setAccessible(true);
            $instances = $property->getValue($this->app);

            foreach (array_keys($instances) as $abstract) {
                $singletons[] = [
                    'abstract' => $abstract,
                    'is_core'  => $this->isCoreBinding($abstract),
                ];
            }
        } catch (\Throwable) {
            //
        }

        return $singletons;
    }

    protected function getRelativePath(?string $path): string
    {
        if (! $path) {
            return '';
        }
        return str_replace(base_path() . '/', '', $path);
    }
}
