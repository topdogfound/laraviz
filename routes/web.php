<?php

use Illuminate\Support\Facades\Route;
use LaraViz\Http\Controllers\LaraVizController;

$prefix     = config('laraviz.route_prefix', 'laraviz');
$middleware = config('laraviz.middleware', ['web']);

Route::middleware($middleware)
    ->prefix($prefix)
    ->name('laraviz.')
    ->group(function () {
        // Catch-all SPA route — Vue Router handles sub-paths
        Route::get('/{any?}', LaraVizController::class)
            ->where('any', '^(?!api).*$')
            ->name('index');
    });
