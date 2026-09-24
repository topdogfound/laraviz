<?php

namespace LaraViz\Analyzers;

use Illuminate\Contracts\Foundation\Application;

class ConfigAnalyzer
{
    // Keys that should never be exposed — even partially
    protected array $sensitiveKeys = [
        'password', 'secret', 'key', 'token', 'private',
        'credential', 'auth', 'cipher', 'hash', 'salt',
        'api_key', 'access_key', 'secret_key', 'webhook',
    ];

    public function __construct(protected Application $app) {}

    public function analyze(): array
    {
        $config = config()->all();
        $sanitized = $this->sanitizeConfig($config);

        return [
            'environment'   => $this->app->environment(),
            'debug'         => config('app.debug', false),
            'cache_driver'  => config('cache.default'),
            'session_driver'=> config('session.driver'),
            'queue_driver'  => config('queue.default'),
            'mail_driver'   => config('mail.default'),
            'filesystem_driver' => config('filesystems.default'),
            'database_default'  => config('database.default'),
            'broadcasting_driver' => config('broadcasting.default'),
            'config_keys'   => array_keys($sanitized),
            'app'           => $sanitized['app'] ?? [],
            'services_configured' => array_keys($sanitized['services'] ?? []),
            'filesystems'   => $this->analyzeFilesystems(),
            'cache'         => $this->analyzeCache(),
            'queue'         => $this->analyzeQueue(),
        ];
    }

    protected function sanitizeConfig(array $config, string $prefix = ''): array
    {
        $result = [];

        foreach ($config as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : $key;

            if ($this->isSensitiveKey($key)) {
                $result[$key] = '*** REDACTED ***';
            } elseif (is_array($value)) {
                $result[$key] = $this->sanitizeConfig($value, $fullKey);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    protected function isSensitiveKey(string $key): bool
    {
        $lower = strtolower($key);
        foreach ($this->sensitiveKeys as $sensitive) {
            if (str_contains($lower, $sensitive)) {
                return true;
            }
        }
        return false;
    }

    protected function analyzeFilesystems(): array
    {
        $filesystems = config('filesystems', []);
        $disks = $filesystems['disks'] ?? [];

        return array_map(fn ($name, $disk) => [
            'name'   => $name,
            'driver' => $disk['driver'] ?? 'unknown',
            'is_default' => $name === config('filesystems.default'),
            'visibility' => $disk['visibility'] ?? 'private',
        ], array_keys($disks), array_values($disks));
    }

    protected function analyzeCache(): array
    {
        $stores = config('cache.stores', []);

        return [
            'default' => config('cache.default'),
            'prefix'  => config('cache.prefix'),
            'stores'  => array_map(fn ($name, $store) => [
                'name'   => $name,
                'driver' => $store['driver'] ?? 'unknown',
            ], array_keys($stores), array_values($stores)),
        ];
    }

    protected function analyzeQueue(): array
    {
        $connections = config('queue.connections', []);

        return [
            'default' => config('queue.default'),
            'failed_table' => config('queue.failed.table'),
            'connections' => array_map(fn ($name, $conn) => [
                'name'   => $name,
                'driver' => $conn['driver'] ?? 'unknown',
                'queue'  => $conn['queue'] ?? 'default',
            ], array_keys($connections), array_values($connections)),
        ];
    }
}
