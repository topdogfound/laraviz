<?php

namespace LaraViz\Analyzers;

use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\Finder\Finder;

class ModelAnalyzer
{
    public function analyze(): array
    {
        $models = $this->discoverModels();
        $analyzed = [];

        foreach ($models as $fqn) {
            try {
                $analyzed[] = $this->analyzeModel($fqn);
            } catch (\Throwable) {
                // skip uninstantiable models
            }
        }

        return [
            'total'  => count($analyzed),
            'models' => $analyzed,
        ];
    }

    protected function discoverModels(): array
    {
        $scanPaths = config('laraviz.scan_paths', [app_path()]);
        $models = [];

        foreach ($scanPaths as $path) {
            if (! is_dir($path)) {
                continue;
            }

            $finder = Finder::create()
                ->files()
                ->name('*.php')
                ->in($path);

            foreach ($finder as $file) {
                $fqn = $this->fqnFromFile($file->getRealPath());
                if ($fqn && $this->isEloquentModel($fqn)) {
                    $models[] = $fqn;
                }
            }
        }

        return array_unique($models);
    }

    protected function fqnFromFile(string $path): ?string
    {
        $contents = file_get_contents($path);

        preg_match('/^namespace\s+([^;]+);/m', $contents, $nsMatch);
        preg_match('/^class\s+(\w+)/m', $contents, $classMatch);

        if (! isset($classMatch[1])) {
            return null;
        }

        $namespace = $nsMatch[1] ?? '';
        return $namespace ? "{$namespace}\\{$classMatch[1]}" : $classMatch[1];
    }

    protected function isEloquentModel(string $fqn): bool
    {
        try {
            if (! class_exists($fqn)) {
                return false;
            }
            return is_subclass_of($fqn, \Illuminate\Database\Eloquent\Model::class);
        } catch (\Throwable) {
            return false;
        }
    }

    protected function analyzeModel(string $fqn): array
    {
        $reflection = new ReflectionClass($fqn);
        $instance = $reflection->newInstanceWithoutConstructor();

        return [
            'class'       => $fqn,
            'short_name'  => class_basename($fqn),
            'table'       => method_exists($instance, 'getTable') ? $instance->getTable() : Str::snake(Str::plural(class_basename($fqn))),
            'fillable'    => method_exists($instance, 'getFillable') ? $instance->getFillable() : [],
            'guarded'     => method_exists($instance, 'getGuarded') ? $instance->getGuarded() : [],
            'hidden'      => method_exists($instance, 'getHidden') ? $instance->getHidden() : [],
            'casts'       => method_exists($instance, 'getCasts') ? $instance->getCasts() : [],
            'timestamps'  => property_exists($instance, 'timestamps') ? $instance->timestamps : true,
            'soft_deletes'=> $this->hasSoftDeletes($reflection),
            'traits'      => $this->getTraits($reflection),
            'relations'   => $this->getRelations($reflection, $instance),
            'scopes'      => $this->getScopes($reflection),
            'observers'   => $this->getObservers($fqn),
            'file'        => $this->getRelativePath($reflection->getFileName()),
        ];
    }

    protected function hasSoftDeletes(ReflectionClass $reflection): bool
    {
        foreach ($reflection->getTraits() as $trait) {
            if (Str::endsWith($trait->getName(), 'SoftDeletes')) {
                return true;
            }
        }

        // Check parent traits recursively
        $parent = $reflection->getParentClass();
        if ($parent) {
            return $this->hasSoftDeletes($parent);
        }

        return false;
    }

    protected function getTraits(ReflectionClass $reflection): array
    {
        $traits = [];
        foreach ($reflection->getTraits() as $trait) {
            $traits[] = class_basename($trait->getName());
        }
        return $traits;
    }

    protected function getRelations(ReflectionClass $reflection, object $instance): array
    {
        $relations = [];
        $relationTypes = [
            'hasOne', 'hasMany', 'belongsTo', 'belongsToMany',
            'hasOneThrough', 'hasManyThrough', 'morphTo',
            'morphOne', 'morphMany', 'morphToMany', 'morphedByMany',
        ];

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->getDeclaringClass()->getName() !== $reflection->getName()) {
                continue;
            }
            if ($method->getNumberOfParameters() > 0) {
                continue;
            }

            try {
                $returnType = $method->getReturnType();
                if ($returnType) {
                    $typeName = $returnType->getName();
                    foreach ($relationTypes as $relType) {
                        if (Str::endsWith($typeName, ucfirst($relType)) || Str::endsWith($typeName, 'Relation')) {
                            $relations[] = [
                                'method' => $method->getName(),
                                'type'   => $typeName,
                            ];
                            break;
                        }
                    }
                }

                // Attempt to call the method and check if it's a relation
                $result = $instance->{$method->getName()}();
                if ($result instanceof \Illuminate\Database\Eloquent\Relations\Relation) {
                    $type = class_basename(get_class($result));
                    $related = class_basename(get_class($result->getRelated()));

                    // Avoid duplicates
                    $exists = array_filter($relations, fn ($r) => $r['method'] === $method->getName());
                    if (! $exists) {
                        $relations[] = [
                            'method'  => $method->getName(),
                            'type'    => $type,
                            'related' => $related,
                        ];
                    } else {
                        // Enrich existing entry
                        foreach ($relations as &$r) {
                            if ($r['method'] === $method->getName()) {
                                $r['type']    = $type;
                                $r['related'] = $related;
                            }
                        }
                        unset($r);
                    }
                }
            } catch (\Throwable) {
                // Relations that need DB won't work here — that's fine
            }
        }

        return $relations;
    }

    protected function getScopes(ReflectionClass $reflection): array
    {
        $scopes = [];
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if (Str::startsWith($method->getName(), 'scope') && $method->getDeclaringClass()->getName() === $reflection->getName()) {
                $scopes[] = lcfirst(Str::after($method->getName(), 'scope'));
            }
        }
        return $scopes;
    }

    protected function getObservers(string $fqn): array
    {
        // Laravel doesn't provide a direct way to list observers at runtime
        // We look for Observer files that might match
        $observers = [];
        $baseName = class_basename($fqn);
        $observerName = "{$baseName}Observer";

        foreach (config('laraviz.scan_paths', [app_path()]) as $path) {
            $found = glob("{$path}/Observers/{$observerName}.php")
                ?: glob("{$path}/{$observerName}.php");

            foreach ($found as $file) {
                $fqnObserver = $this->fqnFromFile($file);
                if ($fqnObserver) {
                    $observers[] = $fqnObserver;
                }
            }
        }

        return $observers;
    }

    protected function getRelativePath(?string $path): string
    {
        if (! $path) {
            return '';
        }
        return str_replace(base_path() . '/', '', $path);
    }
}
