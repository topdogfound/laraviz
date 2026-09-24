<?php

use Illuminate\Support\Facades\Route;
use LaraViz\Http\Controllers\LaraVizApiController;

$prefix     = config('laraviz.route_prefix', 'laraviz');
$middleware = config('laraviz.middleware', ['web']);

Route::middleware($middleware)
    ->prefix("{$prefix}/api")
    ->name('laraviz.api.')
    ->group(function () {
        // Full analysis payload
        Route::get('/',          [LaraVizApiController::class, 'index'])->name('index');

        // Individual section endpoints
        Route::get('/meta',       [LaraVizApiController::class, 'meta'])->name('meta');
        Route::get('/database',   [LaraVizApiController::class, 'database'])->name('database');
        Route::get('/routes',     [LaraVizApiController::class, 'routes'])->name('routes');
        Route::get('/models',     [LaraVizApiController::class, 'models'])->name('models');
        Route::get('/services',   [LaraVizApiController::class, 'services'])->name('services');
        Route::get('/events',     [LaraVizApiController::class, 'events'])->name('events');
        Route::get('/jobs',       [LaraVizApiController::class, 'jobs'])->name('jobs');
        Route::get('/middleware', [LaraVizApiController::class, 'middlewareInfo'])->name('middleware');
        Route::get('/config',     [LaraVizApiController::class, 'config'])->name('config');

        // Cache management
        Route::delete('/cache',   [LaraVizApiController::class, 'flush'])->name('flush');
    });
