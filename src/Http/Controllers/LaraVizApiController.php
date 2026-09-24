<?php

namespace LaraViz\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use LaraViz\LaraViz;
use LaraViz\Analyzers\DatabaseAnalyzer;
use LaraViz\Analyzers\RouteAnalyzer;
use LaraViz\Analyzers\ModelAnalyzer;
use LaraViz\Analyzers\ServiceAnalyzer;
use LaraViz\Analyzers\EventAnalyzer;
use LaraViz\Analyzers\JobAnalyzer;
use LaraViz\Analyzers\MiddlewareAnalyzer;
use LaraViz\Analyzers\ConfigAnalyzer;
use Throwable;

class LaraVizApiController extends Controller
{
    public function __construct(protected LaraViz $laraViz) {}

    /**
     * Return the full analysis payload — cached for performance.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize();

        $ttl = (int) config('laraviz.cache_ttl', 0); // 0 = no cache

        if ($ttl > 0) {
            $data = Cache::remember('laraviz.analysis', $ttl, fn () => $this->laraViz->analyze());
        } else {
            $data = $this->laraViz->analyze();
        }

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * Return only the database section — useful for canvas refresh without a full reload.
     */
    public function database(): JsonResponse
    {
        $this->authorize();

        return $this->runAnalyzer(
            DatabaseAnalyzer::class,
            'database'
        );
    }

    /**
     * Return only the routes section.
     */
    public function routes(): JsonResponse
    {
        $this->authorize();

        return $this->runAnalyzer(
            RouteAnalyzer::class,
            'routes'
        );
    }

    /**
     * Return only the models section.
     */
    public function models(): JsonResponse
    {
        $this->authorize();

        return $this->runAnalyzer(
            ModelAnalyzer::class,
            'models'
        );
    }

    /**
     * Return only the services/providers section.
     */
    public function services(): JsonResponse
    {
        $this->authorize();

        return $this->runAnalyzer(
            ServiceAnalyzer::class,
            'services'
        );
    }

    /**
     * Return only the events/listeners section.
     */
    public function events(): JsonResponse
    {
        $this->authorize();

        return $this->runAnalyzer(
            EventAnalyzer::class,
            'events'
        );
    }

    /**
     * Return only the jobs section.
     */
    public function jobs(): JsonResponse
    {
        $this->authorize();

        return $this->runAnalyzer(
            JobAnalyzer::class,
            'jobs'
        );
    }

    /**
     * Return only the middleware section.
     */
    public function middlewareInfo(): JsonResponse
    {
        $this->authorize();

        return $this->runAnalyzer(
            MiddlewareAnalyzer::class,
            'middleware'
        );
    }

    /**
     * Return config/environment info.
     */
    public function config(): JsonResponse
    {
        $this->authorize();

        return $this->runAnalyzer(
            ConfigAnalyzer::class,
            'config'
        );
    }

    /**
     * Return meta info only (version, environment, timestamp).
     */
    public function meta(): JsonResponse
    {
        $this->authorize();

        return response()->json([
            'success' => true,
            'data'    => [
                'laravel_version' => app()->version(),
                'php_version'     => PHP_VERSION,
                'environment'     => app()->environment(),
                'app_name'        => config('app.name', 'Laravel'),
                'app_url'         => config('app.url', ''),
                'timezone'        => config('app.timezone', 'UTC'),
                'locale'          => config('app.locale', 'en'),
                'generated_at'    => now()->toIso8601String(),
                'laraviz_version' => '1.0.0',
            ],
        ]);
    }

    /**
     * Flush the analysis cache.
     */
    public function flush(): JsonResponse
    {
        $this->authorize();

        Cache::forget('laraviz.analysis');

        return response()->json([
            'success' => true,
            'message' => 'LaraViz cache cleared.',
        ]);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    protected function runAnalyzer(string $analyzerClass, string $key): JsonResponse
    {
        try {
            $analyzer = app($analyzerClass);
            $data     = $analyzer->analyze();

            return response()->json([
                'success' => true,
                'section' => $key,
                'data'    => $data,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'section' => $key,
                'error'   => $e->getMessage(),
                'data'    => null,
            ], 500);
        }
    }

    protected function authorize(): void
    {
        if (! Gate::check('viewLaraViz')) {
            abort(403, 'Unauthorized to access LaraViz API.');
        }
    }
}
