<?php

namespace LaraViz\Analyzers;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Str;

class RouteAnalyzer
{
    public function __construct(protected Router $router) {}

    public function analyze(): array
    {
        $routes = [];

        foreach ($this->router->getRoutes() as $route) {
            /** @var Route $route */
            $routes[] = $this->analyzeRoute($route);
        }

        return [
            'total'   => count($routes),
            'routes'  => $routes,
            'groups'  => $this->groupRoutes($routes),
            'summary' => $this->buildSummary($routes),
        ];
    }

    protected function analyzeRoute(Route $route): array
    {
        $action = $route->getAction();
        $controller = null;
        $controllerMethod = null;
        $isApiRoute = false;

        if (isset($action['controller'])) {
            $parts = explode('@', $action['controller']);
            $controller = $parts[0] ?? null;
            $controllerMethod = $parts[1] ?? '__invoke';
        }

        $middleware = $route->gatherMiddleware();
        $isApiRoute = in_array('api', $middleware, true)
            || Str::startsWith($route->uri(), 'api/')
            || in_array('auth:sanctum', $middleware, true)
            || in_array('auth:api', $middleware, true);

        return [
            'uri'          => $route->uri(),
            'methods'      => $route->methods(),
            'name'         => $route->getName(),
            'controller'   => $controller ? class_basename($controller) : null,
            'controller_fqn' => $controller,
            'method'       => $controllerMethod,
            'middleware'   => $middleware,
            'is_api'       => $isApiRoute,
            'parameters'   => $route->parameterNames(),
            'prefix'       => $route->getPrefix(),
            'domain'       => $route->getDomain(),
            'wheres'       => $route->wheres,
            'uses'         => is_string($action['uses'] ?? null) ? $action['uses'] : null,
        ];
    }

    protected function groupRoutes(array $routes): array
    {
        $groups = [];

        foreach ($routes as $route) {
            // Group by first URI segment
            $segment = explode('/', ltrim($route['uri'], '/'))[0] ?: 'root';
            $segment = Str::before($segment, '{'); // strip param placeholders

            if (! isset($groups[$segment])) {
                $groups[$segment] = [
                    'prefix'   => $segment,
                    'routes'   => [],
                    'is_api'   => false,
                    'route_count' => 0,
                ];
            }

            $groups[$segment]['routes'][] = $route;
            $groups[$segment]['route_count']++;

            if ($route['is_api']) {
                $groups[$segment]['is_api'] = true;
            }
        }

        return array_values($groups);
    }

    protected function buildSummary(array $routes): array
    {
        $methods = [];
        $middleware = [];

        foreach ($routes as $route) {
            foreach ($route['methods'] as $method) {
                $methods[$method] = ($methods[$method] ?? 0) + 1;
            }
            foreach ($route['middleware'] as $mw) {
                $middleware[$mw] = ($middleware[$mw] ?? 0) + 1;
            }
        }

        $apiCount = count(array_filter($routes, fn ($r) => $r['is_api']));
        $webCount = count($routes) - $apiCount;

        return [
            'api_routes'       => $apiCount,
            'web_routes'       => $webCount,
            'methods'          => $methods,
            'top_middleware'   => $middleware,
            'named_routes'     => count(array_filter($routes, fn ($r) => $r['name'])),
            'controllers_used' => array_values(array_unique(array_filter(array_column($routes, 'controller_fqn')))),
        ];
    }
}
