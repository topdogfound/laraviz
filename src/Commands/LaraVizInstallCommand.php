<?php

namespace LaraViz\Commands;

use Illuminate\Console\Command;

class LaraVizInstallCommand extends Command
{
    protected $signature = 'laraviz:install
        {--force : Overwrite existing published files}
        {--skip-assets : Skip publishing compiled assets}';

    protected $description = 'Install the LaraViz package: publish config and assets';

    public function handle(): int
    {
        $this->info('');
        $this->line('  <fg=cyan;options=bold>  ██╗      █████╗ ██████╗  █████╗ ██╗   ██╗██╗███████╗</>');
        $this->line('  <fg=cyan;options=bold>  ██║     ██╔══██╗██╔══██╗██╔══██╗██║   ██║██║╚══███╔╝</>');
        $this->line('  <fg=cyan;options=bold>  ██║     ███████║██████╔╝███████║██║   ██║██║  ███╔╝ </>');
        $this->line('  <fg=cyan;options=bold>  ██║     ██╔══██║██╔══██╗██╔══██║╚██╗ ██╔╝██║ ███╔╝  </>');
        $this->line('  <fg=cyan;options=bold>  ███████╗██║  ██║██║  ██║██║  ██║ ╚████╔╝ ██║███████╗</>');
        $this->line('  <fg=cyan;options=bold>  ╚══════╝╚═╝  ╚═╝╚═╝  ╚═╝╚═╝  ╚═╝  ╚═══╝  ╚═╝╚══════╝</>');
        $this->info('');
        $this->line('  <fg=gray>Interactive Laravel Application Visualizer</fg=gray>');
        $this->info('');

        $force = $this->option('force');

        // Publish config
        $this->publishConfig($force);

        // Publish assets
        if (! $this->option('skip-assets')) {
            $this->publishAssets($force);
        }

        $this->info('');
        $this->line('  <fg=green;options=bold>✔</> LaraViz installed successfully!');
        $this->info('');
        $this->line('  Visit your app at: <fg=cyan>' . url(config('laraviz.route_prefix', 'laraviz')) . '</>');
        $this->info('');
        $this->line('  <fg=yellow>Note:</> LaraViz is only accessible in <fg=cyan>local</> environment by default.');
        $this->line('  Override the <fg=cyan>viewLaraViz</> gate to control access.');
        $this->info('');

        return self::SUCCESS;
    }

    protected function publishConfig(bool $force): void
    {
        $this->call('vendor:publish', [
            '--tag'   => 'laraviz-config',
            '--force' => $force,
        ]);

        $this->line('  <fg=green>✔</> Config published to <fg=cyan>config/laraviz.php</>');
    }

    protected function publishAssets(bool $force): void
    {
        $this->call('vendor:publish', [
            '--tag'   => 'laraviz-assets',
            '--force' => $force,
        ]);

        $this->line('  <fg=green>✔</> Assets published to <fg=cyan>public/vendor/laraviz/</>');
    }
}
