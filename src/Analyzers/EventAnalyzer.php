<?php

namespace LaraViz\Analyzers;

use Illuminate\Support\Facades\Event;
use ReflectionClass;

class EventAnalyzer
{
    public function analyze(): array
    {
        $rawListeners = Event::getRawListeners();
        $events = [];

        foreach ($rawListeners as $eventClass => $listeners) {
            $events[] = [
                'event'       => $eventClass,
                'short_name'  => class_basename($eventClass),
                'is_wildcard' => str_contains($eventClass, '*'),
                'listeners'   => $this->analyzeListeners($listeners),
                'details'     => $this->analyzeEventClass($eventClass),
            ];
        }

        // Also discover event/listener classes by scanning
        $discovered = $this->discoverEventClasses();

        return [
            'total'      => count($events),
            'events'     => $events,
            'discovered' => $discovered,
            'summary'    => [
                'total_events'    => count($events),
                'total_listeners' => array_sum(array_map(fn ($e) => count($e['listeners']), $events)),
                'wildcard_events' => count(array_filter($events, fn ($e) => $e['is_wildcard'])),
            ],
        ];
    }

    protected function analyzeListeners(array $listeners): array
    {
        $analyzed = [];

        foreach ($listeners as $listener) {
            if ($listener instanceof \Closure) {
                $analyzed[] = [
                    'type'      => 'closure',
                    'class'     => null,
                    'method'    => null,
                    'is_queued' => false,
                ];
            } elseif (is_string($listener)) {
                $parts = explode('@', $listener);
                $class  = $parts[0];
                $method = $parts[1] ?? 'handle';

                $analyzed[] = [
                    'type'      => 'class',
                    'class'     => $class,
                    'short_name'=> class_basename($class),
                    'method'    => $method,
                    'is_queued' => $this->isQueuedListener($class),
                ];
            } elseif (is_array($listener)) {
                // [ClassName, 'method'] format
                $class  = is_object($listener[0]) ? get_class($listener[0]) : $listener[0];
                $method = $listener[1] ?? 'handle';

                $analyzed[] = [
                    'type'      => 'class',
                    'class'     => $class,
                    'short_name'=> class_basename($class),
                    'method'    => $method,
                    'is_queued' => $this->isQueuedListener($class),
                ];
            }
        }

        return $analyzed;
    }

    protected function isQueuedListener(string $class): bool
    {
        try {
            return is_subclass_of($class, \Illuminate\Contracts\Queue\ShouldQueue::class);
        } catch (\Throwable) {
            return false;
        }
    }

    protected function analyzeEventClass(string $eventClass): ?array
    {
        try {
            if (! class_exists($eventClass)) {
                return null;
            }

            $reflection = new ReflectionClass($eventClass);
            return [
                'broadcasts'  => is_subclass_of($eventClass, \Illuminate\Contracts\Broadcasting\ShouldBroadcast::class),
                'properties'  => array_map(
                    fn ($prop) => ['name' => $prop->getName(), 'public' => $prop->isPublic()],
                    $reflection->getProperties(\ReflectionProperty::IS_PUBLIC)
                ),
                'file' => $this->getRelativePath($reflection->getFileName()),
            ];
        } catch (\Throwable) {
            return null;
        }
    }

    protected function discoverEventClasses(): array
    {
        $scanPaths = config('laraviz.scan_paths', [app_path()]);
        $events = [];

        foreach ($scanPaths as $path) {
            foreach (['Events', 'Listeners', 'Observers'] as $dir) {
                $dirPath = rtrim($path, '/') . '/' . $dir;
                if (! is_dir($dirPath)) {
                    continue;
                }

                foreach (glob("{$dirPath}/*.php") as $file) {
                    $events[] = [
                        'file' => $this->getRelativePath($file),
                        'type' => strtolower(rtrim($dir, 's')),
                        'name' => basename($file, '.php'),
                    ];
                }
            }
        }

        return $events;
    }

    protected function getRelativePath(?string $path): string
    {
        if (! $path) {
            return '';
        }
        return str_replace(base_path() . '/', '', $path);
    }
}
