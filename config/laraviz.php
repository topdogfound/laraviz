<?php

return [

    /*
    |--------------------------------------------------------------------------
    | LaraViz Enabled
    |--------------------------------------------------------------------------
    | Enable or disable the LaraViz dashboard. It is strongly recommended to
    | keep this disabled in production environments.
    */
    'enabled' => env('LARAVIZ_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Route Prefix & Middleware
    |--------------------------------------------------------------------------
    | The URI prefix under which LaraViz will be accessible.
    | Default: /laraviz
    */
    'route_prefix' => env('LARAVIZ_ROUTE_PREFIX', 'laraviz'),

    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Authorization Gate
    |--------------------------------------------------------------------------
    | LaraViz registers a gate called "viewLaraViz". Override it in your
    | AuthServiceProvider to restrict access in non-local environments.
    */
    'gate' => 'viewLaraViz',

    /*
    |--------------------------------------------------------------------------
    | Analyzers
    |--------------------------------------------------------------------------
    | Toggle individual analyzers on or off.
    */
    'analyzers' => [
        'database'   => true,
        'routes'     => true,
        'models'     => true,
        'services'   => true,
        'events'     => true,
        'jobs'       => true,
        'middleware' => true,
        'commands'   => true,
        'config'     => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Scan Paths
    |--------------------------------------------------------------------------
    | Directories LaraViz will scan when introspecting your application.
    */
    'scan_paths' => [
        app_path(),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database
    |--------------------------------------------------------------------------
    | Which database connections to include.
    */
    'database' => [
        'connections' => ['default'],
        'exclude_tables' => [
            'migrations',
            'password_resets',
            'password_reset_tokens',
            'failed_jobs',
            'personal_access_tokens',
            'cache',
            'cache_locks',
            'sessions',
            'jobs',
            'job_batches',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Theme
    |--------------------------------------------------------------------------
    | 'dark' or 'light'. Dark is the default futuristic theme.
    */
    'theme' => env('LARAVIZ_THEME', 'dark'),

];
