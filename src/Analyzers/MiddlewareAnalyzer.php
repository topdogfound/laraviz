<?php

namespace LaraViz\Analyzers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Router;
use ReflectionClass;

class MiddlewareAnalyzer
{
    public function __construct(
        protected Application $app,
        protected Router $router
    ) {}

    public function analyze(): array
    {
        return [
            'registered'  => $this->getRegisteredMiddleware(),
            'groups'      => $this->getMiddlewareGroups(),
            'route_usage' => $this->getRouteMiddlewareUsage(),
        ];
    }

    protected function getRegisteredMiddleware(): array
    {
        $middleware = [];

        // Global middleware from the kernel
        try {
            $kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);
            $reflection = new ReflectionClass($kernel);

            foreach (['middleware', 'middlewareGroups', 'routeMiddleware', 'middlewareAliases'] as $prop) {
                if ($reflection->hasProperty($prop)) {
                    $property = $reflection->getProperty($prop);
                    $property->setAccessible(true);
                    $value = $property->getValue($kernel);

                    if ($prop === 'middleware') {
                        foreach ($value as $mw) {
                            $middleware[] = $this->analyzeMiddlewareClass($mw, 'global');
                        }
                    }
                }
            }
        } catch (\Throwable) {
            // Kernel not available or different structure
        }

        return $middleware;
    }

    protected function getMiddlewareGroups(): array
    {
        $groups = [];

        try {
            $kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);
            $reflection = new ReflectionClass($kernel);

            foreach (['middlewareGroups'] as $prop) {
                if ($reflection->hasProperty($prop)) {
                    $property = $reflection->getProperty($prop);
                    $property->setAccessible(true);
                    $groupsRaw = $property->getValue($kernel);

                    foreach ($groupsRaw as $groupName => $mws) {
                        $groups[$groupName] = [
                            'name'       => $groupName,
                            'middleware' => array_map(
                                fn ($mw) => $this->analyzeMiddlewareClass(
                                    is_string($mw) ? $mw : get_class($mw),
                                    'group'
                                ),
                                $mws
                            ),
                        ];
                    }
                }
            }

            // Also get route middleware / aliases
            foreach (['routeMiddleware', 'middlewareAliases'] as $prop) {
                if ($reflection->hasProperty($prop)) {
                    $property = $reflection->getProperty($prop);
                    $property->setAccessible(true);
                    $aliases = $property->getValue($kernel);

                    $groups['__aliases'] = [
                        'name'       => 'Route Aliases',
                        'middleware' => array_map(
                            fn ($alias, $class) => array_merge(
                                $this->analyzeMiddlewareClass($class, 'alias'),
                                ['alias' => $alias]
                            ),
                            array_keys($aliases),
                            array_values($aliases)
                        ),
                    ];
                }
            }
        } catch (\Throwable) {
            //
        }

        return $groups;
    }

    protected function analyzeMiddlewareClass(string $class, string $scope): array
    {
        $isCore = str_starts_with($class, 'Illuminate\\')
               || str_starts_with($class, 'Laravel\\');

        return [
            'class'      => $class,
            'short_name' => class_basename($class),
            'scope'      => $scope,
            'is_core'    => $isCore,
            'terminable' => $this->isTerminable($class),
        ];
    }

    protected function isTerminable(string $class): bool
    {
        try {
            return is_subclass_of($class, \Illuminate\Contracts\Http\Middleware\TerminableMiddleware::class)
                || (class_exists($class) && method_exists($class, 'terminate'));
        } catch (\Throwable) {
            return false;
        }
    }

    protected function getRouteMiddlewareUsage(): array
    {
        $usage = [];

        foreach ($this->router->getRoutes() as $route) {
            foreach ($route->gatherMiddleware() as $mw) {
                $usage[$mw] = ($usage[$mw] ?? 0) + 1;
            }
        }

        arsort($usage);

        return array_map(
            fn ($mw, $count) => ['middleware' => $mw, 'route_count' => $count],
            array_keys($usage),
            array_values($usage)
        );
    }
}
