<?php

namespace LaraViz\Commands;

use Illuminate\Console\Command;
use LaraViz\LaraViz;

class LaraVizAnalyzeCommand extends Command
{
    protected $signature = 'laraviz:analyze
        {--section= : Run only one section (database|routes|models|services|events|jobs)}
        {--json     : Output raw JSON}
        {--pretty   : Pretty-print JSON output}';

    protected $description = 'Run LaraViz analyzers and display a summary (or export JSON)';

    public function handle(LaraViz $laraViz): int
    {
        $this->info('');
        $this->line('  <fg=cyan;options=bold>LaraViz Analysis</> — <fg=gray>' . config('app.name') . '</>');
        $this->info('');

        $section = $this->option('section');

        try {
            if ($section) {
                $data = $this->runSection($section);
            } else {
                $data = $laraViz->analyze();
            }
        } catch (\Throwable $e) {
            $this->error('Analysis failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        if ($this->option('json') || $this->option('pretty')) {
            $flags = $this->option('pretty') ? JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES : 0;
            $this->line(json_encode($data, $flags));
            return self::SUCCESS;
        }

        $this->displaySummary($data);
        return self::SUCCESS;
    }

    protected function runSection(string $section): array
    {
        $map = [
            'database'   => \LaraViz\Analyzers\DatabaseAnalyzer::class,
            'routes'     => \LaraViz\Analyzers\RouteAnalyzer::class,
            'models'     => \LaraViz\Analyzers\ModelAnalyzer::class,
            'services'   => \LaraViz\Analyzers\ServiceAnalyzer::class,
            'events'     => \LaraViz\Analyzers\EventAnalyzer::class,
            'jobs'       => \LaraViz\Analyzers\JobAnalyzer::class,
            'middleware' => \LaraViz\Analyzers\MiddlewareAnalyzer::class,
            'config'     => \LaraViz\Analyzers\ConfigAnalyzer::class,
        ];

        if (! isset($map[$section])) {
            $this->error("Unknown section: {$section}. Available: " . implode(', ', array_keys($map)));
            exit(self::FAILURE);
        }

        return app($map[$section])->analyze();
    }

    protected function displaySummary(array $data): void
    {
        $meta = $data['meta'] ?? [];

        $this->table(
            ['Key', 'Value'],
            [
                ['Laravel',     $meta['laravel_version'] ?? 'unknown'],
                ['PHP',         $meta['php_version']     ?? 'unknown'],
                ['Environment', $meta['environment']     ?? 'unknown'],
                ['App',         $meta['app_name']        ?? 'unknown'],
                ['Timezone',    $meta['timezone']        ?? 'UTC'],
                ['Generated',   $meta['generated_at']   ?? now()->toIso8601String()],
            ]
        );

        // Database
        if (isset($data['database'])) {
            foreach ($data['database'] as $connName => $conn) {
                if (isset($conn['error'])) {
                    $this->warn("  Database [{$connName}]: {$conn['error']}");
                    continue;
                }
                $tableCount = count($conn['tables'] ?? []);
                $fkCount    = count($conn['foreign_keys'] ?? []);
                $this->line("  <fg=cyan>Database</> [{$connName} / {$conn['driver']}]: <fg=white>{$tableCount}</> tables, <fg=white>{$fkCount}</> foreign keys");
            }
        }

        // Routes
        if (isset($data['routes']['summary'])) {
            $s = $data['routes']['summary'];
            $this->line("  <fg=yellow>Routes</>: <fg=white>{$data['routes']['total']}</> total — {$s['api_routes']} API, {$s['web_routes']} web, {$s['named_routes']} named");
        }

        // Models
        if (isset($data['models']['total'])) {
            $this->line("  <fg=green>Models</>: <fg=white>{$data['models']['total']}</> Eloquent models discovered");
        }

        // Services
        if (isset($data['services']['providers'])) {
            $appProviders = count(array_filter($data['services']['providers'], fn ($p) => ! $p['is_core']));
            $this->line("  <fg=magenta>Services</>: <fg=white>{$appProviders}</> app providers, " . count($data['services']['facades'] ?? []) . " facades");
        }

        // Events
        if (isset($data['events']['total'])) {
            $this->line("  <fg=magenta>Events</>: <fg=white>{$data['events']['total']}</> events, {$data['events']['summary']['total_listeners']} listeners");
        }

        // Jobs
        if (isset($data['jobs']['total'])) {
            $this->line("  <fg=yellow>Jobs</>: <fg=white>{$data['jobs']['total']}</> jobs ({$data['jobs']['summary']['queued']} queued)");
        }

        $this->info('');
        $this->line('  Visit: <fg=cyan>' . url(config('laraviz.route_prefix', 'laraviz')) . '</>');
        $this->info('');
    }
}
