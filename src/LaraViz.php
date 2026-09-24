<?php

namespace LaraViz;

use Illuminate\Contracts\Foundation\Application;
use LaraViz\Analyzers\DatabaseAnalyzer;
use LaraViz\Analyzers\RouteAnalyzer;
use LaraViz\Analyzers\ModelAnalyzer;
use LaraViz\Analyzers\ServiceAnalyzer;
use LaraViz\Analyzers\EventAnalyzer;
use LaraViz\Analyzers\JobAnalyzer;
use LaraViz\Analyzers\MiddlewareAnalyzer;
use LaraViz\Analyzers\ConfigAnalyzer;

class LaraViz
{
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Run all enabled analyzers and return the full visualization payload.
     */
    public function analyze(): array
    {
        $config = config('laraviz.analyzers', []);

        return [
            'meta' => $this->getMeta(),
            'database'   => ($config['database'] ?? true)   ? $this->app->make(DatabaseAnalyzer::class)->analyze()   : null,
            'routes'     => ($config['routes'] ?? true)     ? $this->app->make(RouteAnalyzer::class)->analyze()      : null,
            'models'     => ($config['models'] ?? true)     ? $this->app->make(ModelAnalyzer::class)->analyze()      : null,
            'services'   => ($config['services'] ?? true)   ? $this->app->make(ServiceAnalyzer::class)->analyze()    : null,
            'events'     => ($config['events'] ?? true)     ? $this->app->make(EventAnalyzer::class)->analyze()      : null,
            'jobs'       => ($config['jobs'] ?? true)       ? $this->app->make(JobAnalyzer::class)->analyze()        : null,
            'middleware' => ($config['middleware'] ?? true)  ? $this->app->make(MiddlewareAnalyzer::class)->analyze() : null,
            'config'     => ($config['config'] ?? true)     ? $this->app->make(ConfigAnalyzer::class)->analyze()     : null,
        ];
    }

    protected function getMeta(): array
    {
        return [
            'laravel_version' => $this->app->version(),
            'php_version'     => PHP_VERSION,
            'environment'     => $this->app->environment(),
            'app_name'        => config('app.name', 'Laravel'),
            'app_url'         => config('app.url', ''),
            'timezone'        => config('app.timezone', 'UTC'),
            'locale'          => config('app.locale', 'en'),
            'generated_at'    => now()->toIso8601String(),
        ];
    }
}
