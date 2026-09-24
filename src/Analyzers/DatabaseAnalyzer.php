<?php

namespace LaraViz\Analyzers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Connection;

class DatabaseAnalyzer
{
    public function analyze(): array
    {
        $connections = $this->getConnections();
        $result = [];

        foreach ($connections as $connectionName) {
            try {
                $result[$connectionName] = $this->analyzeConnection($connectionName);
            } catch (\Throwable $e) {
                $result[$connectionName] = ['error' => $e->getMessage()];
            }
        }

        return $result;
    }

    protected function getConnections(): array
    {
        $configured = config('laraviz.database.connections', ['default']);

        return array_map(
            fn ($c) => $c === 'default' ? config('database.default') : $c,
            $configured
        );
    }

    protected function analyzeConnection(string $connectionName): array
    {
        /** @var Connection $connection */
        $connection = DB::connection($connectionName);
        $driver = $connection->getDriverName();
        $tables = $this->getTables($connection, $driver);
        $excluded = config('laraviz.database.exclude_tables', []);

        $analyzedTables = [];
        foreach ($tables as $table) {
            if (in_array($table, $excluded, true)) {
                continue;
            }
            $analyzedTables[$table] = $this->analyzeTable($connection, $table, $driver);
        }

        return [
            'connection'   => $connectionName,
            'driver'       => $driver,
            'database'     => $connection->getDatabaseName(),
            'tables'       => $analyzedTables,
            'foreign_keys' => $this->getForeignKeys($connection, $driver, array_keys($analyzedTables)),
        ];
    }

    protected function getTables(Connection $connection, string $driver): array
    {
        return match ($driver) {
            'sqlite'  => $this->getSqliteTables($connection),
            'pgsql'   => $this->getPgsqlTables($connection),
            default   => $this->getMysqlTables($connection),
        };
    }

    protected function getMysqlTables(Connection $connection): array
    {
        $rows = $connection->select('SHOW TABLES');
        return array_map(fn ($r) => array_values((array) $r)[0], $rows);
    }

    protected function getSqliteTables(Connection $connection): array
    {
        $rows = $connection->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
        return array_column($rows, 'name');
    }

    protected function getPgsqlTables(Connection $connection): array
    {
        $rows = $connection->select("SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname = 'public'");
        return array_column($rows, 'tablename');
    }

    protected function analyzeTable(Connection $connection, string $table, string $driver): array
    {
        $columns = $this->getColumns($connection, $table, $driver);
        $indexes = $this->getIndexes($connection, $table, $driver);

        return [
            'name'    => $table,
            'columns' => $columns,
            'indexes' => $indexes,
            'row_count' => $this->getRowCount($connection, $table),
        ];
    }

    protected function getColumns(Connection $connection, string $table, string $driver): array
    {
        return match ($driver) {
            'sqlite'  => $this->getSqliteColumns($connection, $table),
            'pgsql'   => $this->getPgsqlColumns($connection, $table),
            default   => $this->getMysqlColumns($connection, $table),
        };
    }

    protected function getMysqlColumns(Connection $connection, string $table): array
    {
        $rows = $connection->select("DESCRIBE `{$table}`");
        return array_map(fn ($r) => [
            'name'     => $r->Field,
            'type'     => $r->Type,
            'nullable' => $r->Null === 'YES',
            'key'      => $r->Key,
            'default'  => $r->Default,
            'extra'    => $r->Extra,
        ], $rows);
    }

    protected function getSqliteColumns(Connection $connection, string $table): array
    {
        $rows = $connection->select("PRAGMA table_info(`{$table}`)");
        return array_map(fn ($r) => [
            'name'     => $r->name,
            'type'     => $r->type,
            'nullable' => ! $r->notnull,
            'key'      => $r->pk ? 'PRI' : '',
            'default'  => $r->dflt_value,
            'extra'    => '',
        ], $rows);
    }

    protected function getPgsqlColumns(Connection $connection, string $table): array
    {
        $rows = $connection->select("
            SELECT column_name, data_type, is_nullable, column_default
            FROM information_schema.columns
            WHERE table_name = ?
            ORDER BY ordinal_position
        ", [$table]);

        return array_map(fn ($r) => [
            'name'     => $r->column_name,
            'type'     => $r->data_type,
            'nullable' => $r->is_nullable === 'YES',
            'key'      => '',
            'default'  => $r->column_default,
            'extra'    => '',
        ], $rows);
    }

    protected function getIndexes(Connection $connection, string $table, string $driver): array
    {
        try {
            return match ($driver) {
                'sqlite' => $this->getSqliteIndexes($connection, $table),
                'pgsql'  => $this->getPgsqlIndexes($connection, $table),
                default  => $this->getMysqlIndexes($connection, $table),
            };
        } catch (\Throwable) {
            return [];
        }
    }

    protected function getMysqlIndexes(Connection $connection, string $table): array
    {
        $rows = $connection->select("SHOW INDEX FROM `{$table}`");
        $indexes = [];
        foreach ($rows as $row) {
            $name = $row->Key_name;
            if (! isset($indexes[$name])) {
                $indexes[$name] = [
                    'name'    => $name,
                    'columns' => [],
                    'unique'  => ! $row->Non_unique,
                    'primary' => $name === 'PRIMARY',
                ];
            }
            $indexes[$name]['columns'][] = $row->Column_name;
        }
        return array_values($indexes);
    }

    protected function getSqliteIndexes(Connection $connection, string $table): array
    {
        $rows = $connection->select("PRAGMA index_list(`{$table}`)");
        $indexes = [];
        foreach ($rows as $row) {
            $info = $connection->select("PRAGMA index_info(`{$row->name}`)");
            $indexes[] = [
                'name'    => $row->name,
                'columns' => array_column($info, 'name'),
                'unique'  => (bool) $row->unique,
                'primary' => $row->origin === 'pk',
            ];
        }
        return $indexes;
    }

    protected function getPgsqlIndexes(Connection $connection, string $table): array
    {
        $rows = $connection->select("
            SELECT indexname, indexdef
            FROM pg_indexes
            WHERE tablename = ?
        ", [$table]);

        return array_map(fn ($r) => [
            'name'    => $r->indexname,
            'columns' => [],
            'unique'  => str_contains($r->indexdef, 'UNIQUE'),
            'primary' => str_contains(strtolower($r->indexname), 'pkey'),
        ], $rows);
    }

    protected function getRowCount(Connection $connection, string $table): int
    {
        try {
            return (int) $connection->table($table)->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    protected function getForeignKeys(Connection $connection, string $driver, array $tables): array
    {
        $foreignKeys = [];

        foreach ($tables as $table) {
            try {
                $keys = match ($driver) {
                    'sqlite' => $this->getSqliteForeignKeys($connection, $table),
                    'pgsql'  => $this->getPgsqlForeignKeys($connection, $table),
                    default  => $this->getMysqlForeignKeys($connection, $table),
                };
                $foreignKeys = array_merge($foreignKeys, $keys);
            } catch (\Throwable) {
                // skip tables with errors
            }
        }

        return $foreignKeys;
    }

    protected function getMysqlForeignKeys(Connection $connection, string $table): array
    {
        $database = $connection->getDatabaseName();
        $rows = $connection->select("
            SELECT
                kcu.CONSTRAINT_NAME as constraint_name,
                kcu.TABLE_NAME as from_table,
                kcu.COLUMN_NAME as from_column,
                kcu.REFERENCED_TABLE_NAME as to_table,
                kcu.REFERENCED_COLUMN_NAME as to_column,
                rc.UPDATE_RULE as on_update,
                rc.DELETE_RULE as on_delete
            FROM information_schema.KEY_COLUMN_USAGE kcu
            JOIN information_schema.REFERENTIAL_CONSTRAINTS rc
                ON kcu.CONSTRAINT_NAME = rc.CONSTRAINT_NAME
                AND kcu.CONSTRAINT_SCHEMA = rc.CONSTRAINT_SCHEMA
            WHERE kcu.TABLE_SCHEMA = ?
                AND kcu.TABLE_NAME = ?
                AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
        ", [$database, $table]);

        return array_map(fn ($r) => [
            'name'       => $r->constraint_name,
            'from_table' => $r->from_table,
            'from_column'=> $r->from_column,
            'to_table'   => $r->to_table,
            'to_column'  => $r->to_column,
            'on_update'  => $r->on_update,
            'on_delete'  => $r->on_delete,
        ], $rows);
    }

    protected function getSqliteForeignKeys(Connection $connection, string $table): array
    {
        $rows = $connection->select("PRAGMA foreign_key_list(`{$table}`)");
        return array_map(fn ($r) => [
            'name'       => "fk_{$table}_{$r->from}",
            'from_table' => $table,
            'from_column'=> $r->from,
            'to_table'   => $r->table,
            'to_column'  => $r->to,
            'on_update'  => $r->on_update,
            'on_delete'  => $r->on_delete,
        ], $rows);
    }

    protected function getPgsqlForeignKeys(Connection $connection, string $table): array
    {
        $rows = $connection->select("
            SELECT
                tc.constraint_name,
                kcu.table_name AS from_table,
                kcu.column_name AS from_column,
                ccu.table_name AS to_table,
                ccu.column_name AS to_column,
                rc.update_rule AS on_update,
                rc.delete_rule AS on_delete
            FROM information_schema.table_constraints tc
            JOIN information_schema.key_column_usage kcu
                ON tc.constraint_name = kcu.constraint_name
            JOIN information_schema.referential_constraints rc
                ON tc.constraint_name = rc.constraint_name
            JOIN information_schema.constraint_column_usage ccu
                ON ccu.constraint_name = tc.constraint_name
            WHERE tc.constraint_type = 'FOREIGN KEY'
                AND tc.table_name = ?
        ", [$table]);

        return array_map(fn ($r) => [
            'name'       => $r->constraint_name,
            'from_table' => $r->from_table,
            'from_column'=> $r->from_column,
            'to_table'   => $r->to_table,
            'to_column'  => $r->to_column,
            'on_update'  => $r->on_update,
            'on_delete'  => $r->on_delete,
        ], $rows);
    }
}
