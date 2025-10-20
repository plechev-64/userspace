<?php

namespace UserSpace\Core\Database;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Описывает интерфейс для конструктора SQL-запросов.
 */
interface QueryBuilderInterface
{
    public function getConnection(): DatabaseConnectionInterface;

    public function select(array|string $columns = ['*']): self;

    public function from(string $table, ?string $alias = null): self;

    public function table(string $table, ?string $alias = null): self;

    public function where(string|callable $column, ?string $operator = null, mixed $value = null): self;

    public function orWhere(string|callable $column, ?string $operator = null, mixed $value = null): self;

    public function leftJoin(string $table, string $on): self;

    public function addJoin(string $type, string $table, ?string $alias, string $on): self;

    public function orderBy(string $column, string $direction = 'ASC'): self;

    public function limit(int $limit): self;

    public function offset(int $offset): self;

    /**
     * @return array<object>
     */
    public function get(): array;

    public function first(): ?object;

    public function value(string $column): mixed;

    public function count(string $column = '*'): int;

    /**
     * @return array{0: string, 1: array}
     */
    public function buildQuery(): array;

    /**
     * @param array<string, mixed> $data
     * @return int|false
     */
    public function insert(array $data): int|false;

    /**
     * @param array<string, mixed> $data
     * @return int|false
     */
    public function update(array $data): int|false;

    public function delete(): int|false;

    public function getTableName(string $tableName): string;

    public function firstRaw(string $query, ...$args): ?object;
}