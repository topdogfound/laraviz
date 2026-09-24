<?php

namespace LaraViz;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use LaraViz\Commands\LaraVizInstallCommand;
use LaraViz\Http\Controllers\LaraVizController;
use LaraViz\Http\Controllers\LaraVizApiController;

class LaraVizServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/laraviz.php',
            'laraviz'
        );

        $this->app->singleton(LaraViz::class, fn ($app) => new LaraViz($app));
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerPublishing();
        $this->registerRoutes();
        $this->registerViews();
        $this->registerCommands();
        $this->registerGate();
    }

    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            // Config
            $this->publishes([
                __DIR__ . '/../config/laraviz.php' => config_path('laraviz.php'),
            ], 'laraviz-config');

            // Public assets
            $this->publishes([
                __DIR__ . '/../public/laraviz' => public_path('vendor/laraviz'),
            ], 'laraviz-assets');

            // Views
            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/laraviz'),
            ], 'laraviz-views');
        }
    }

    protected function registerRoutes(): void
    {
        if (! config('laraviz.enabled', true)) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
    }

    protected function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'laraviz');
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                LaraVizInstallCommand::class,
                \LaraViz\Commands\LaraVizAnalyzeCommand::class,
            ]);
        }
    }

    protected function registerGate(): void
    {
        Gate::define('viewLaraViz', function ($user = null) {
            return $this->app->environment('local');
        });
    }
}
