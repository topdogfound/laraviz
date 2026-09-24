# 🔭 LaraViz

**Interactive canvas-based visualization of your entire Laravel application.**

Database schema, Eloquent models, routes, service providers, events, jobs, and the full request lifecycle — all on a zoomable, pannable, interactive canvas with a futuristic dark UI.

---

## Features

| Canvas | What you see |
|--------|-------------|
| **Overview** | App stats, config snapshot, quick navigation |
| **Database** | Tables, columns, indexes, foreign key edges (MySQL / SQLite / PostgreSQL) |
| **Routes** | All routes grouped or flat, with method badges, middleware, controller links |
| **Models** | Eloquent model graph with relationship edges (HasMany, BelongsTo, etc.) |
| **Services** | Service providers, container bindings, facades |
| **Events** | Events → Listeners fan-out with queued indicators |
| **Jobs** | Queue jobs with driver, queue name, tries, timeout |
| **App Flow** | Request lifecycle, Queue flow, Event dispatch flow |

---

## Installation

```bash
composer require laraviz/laraviz --dev
```

Publish config and assets:

```bash
php artisan laraviz:install
```

Then open **`/laraviz`** in your browser.

---

## Requirements

- PHP 8.1+
- Laravel 10, 11, or 12
- Node.js 18+ (for building frontend assets from source)

---

## Configuration

`config/laraviz.php` is published on install. Key options:

```php
// Restrict access — override in AuthServiceProvider
Gate::define('viewLaraViz', function ($user = null) {
    return app()->environment('local');
});
```

```php
// config/laraviz.php
'enabled'      => env('LARAVIZ_ENABLED', true),
'route_prefix' => env('LARAVIZ_ROUTE_PREFIX', 'laraviz'),
'middleware'   => ['web'],
'theme'        => 'dark',

'database' => [
    'connections'    => ['default'],
    'exclude_tables' => ['migrations', 'sessions', /* … */],
],
```

---

## Artisan Commands

```bash
# Install / publish assets
php artisan laraviz:install

# Run all analyzers and print a summary
php artisan laraviz:analyze

# Analyze a single section
php artisan laraviz:analyze --section=database

# Export as JSON
php artisan laraviz:analyze --json --pretty > laraviz-snapshot.json
```

---

## REST API

All endpoints are prefixed at `/laraviz/api/` and respect the `viewLaraViz` gate.

| Method | Path | Description |
|--------|------|-------------|
| `GET` | `/laraviz/api/` | Full analysis payload |
| `GET` | `/laraviz/api/meta` | App meta (version, env) |
| `GET` | `/laraviz/api/database` | Database schema |
| `GET` | `/laraviz/api/routes` | All routes |
| `GET` | `/laraviz/api/models` | Eloquent models |
| `GET` | `/laraviz/api/services` | Service providers & bindings |
| `GET` | `/laraviz/api/events` | Events & listeners |
| `GET` | `/laraviz/api/jobs` | Queue jobs |
| `GET` | `/laraviz/api/middleware` | Middleware |
| `GET` | `/laraviz/api/config` | Sanitized config |
| `DELETE` | `/laraviz/api/cache` | Flush analysis cache |

---

## Building Frontend Assets

```bash
cd /path/to/vendor/laraviz/laraviz
npm install
npm run build
php artisan vendor:publish --tag=laraviz-assets --force
```

For development with hot-reload:

```bash
npm run dev
# set LARAVIZ_VITE_DEV=true in .env, then visit /laraviz
```

---

## Security

- LaraViz is **disabled by default in production** via the `viewLaraViz` gate.
- The `ConfigAnalyzer` redacts all keys containing `password`, `secret`, `key`, `token`, etc.
- No data leaves your server — the frontend is a pure SPA served from your own app.
- Add `LARAVIZ_ENABLED=false` to `.env` to disable completely.

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| PHP Backend | Laravel Service Provider, Artisan Commands |
| Analyzers | Reflection, DB introspection, Route/Event facade |
| Frontend | Vue 3 + Vite |
| Canvas | [@xyflow/vue](https://github.com/xyflow/xyflow) (Vue Flow) |
| State | Pinia |
| Routing | Vue Router |
| Styling | Tailwind CSS v3 |
| HTTP | Axios |

---

## License

MIT
