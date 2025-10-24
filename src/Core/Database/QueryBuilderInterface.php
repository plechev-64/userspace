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
    /**
     * Возвращает экземпляр соединения с базой данных.
     *
     * @return DatabaseConnectionInterface
     */
    public function getConnection(): DatabaseConnectionInterface;

    /**
     * Устанавливает столбцы для выборки.
     *
     * @param array|string $columns Столбцы для выборки.
     * @return $this
     */
    public function select(array|string $columns = ['*']): self;

    /**
     * Устанавливает таблицу для запроса.
     *
     * @param string $table Имя таблицы.
     * @param string|null $alias Псевдоним таблицы.
     * @return $this
     */
    public function from(string $table, ?string $alias = null): self;

    /**
     * Псевдоним для метода from().
     *
     * @param string $table Имя таблицы.
     * @param string|null $alias Псевдоним таблицы.
     * @return $this
     */
    public function table(string $table, ?string $alias = null): self;

    /**
     * Добавляет условие WHERE.
     *
     * @param string|callable $column Столбец или замыкание для группировки условий.
     * @param string|null $operator Оператор сравнения.
     * @param mixed|null $value Значение для сравнения.
     * @return $this
     */
    public function where(string|callable $column, ?string $operator = null, mixed $value = null): self;

    /**
     * Добавляет условие OR WHERE.
     *
     * @param string|callable $column Столбец или замыкание для группировки условий.
     * @param string|null $operator Оператор сравнения.
     * @param mixed|null $value Значение для сравнения.
     * @return $this
     */
    public function orWhere(string|callable $column, ?string $operator = null, mixed $value = null): self;

    /**
     * Добавляет условие WHERE ... IS NULL.
     *
     * @param string $column Столбец.
     * @return $this
     */
    public function whereNull(string $column): self;

    /**
     * Добавляет условие OR WHERE ... IS NULL.
     *
     * @param string $column Столбец.
     * @return $this
     */
    public function orWhereNull(string $column): self;

    /**
     * Добавляет LEFT JOIN к запросу.
     *
     * @param string $table Таблица для присоединения.
     * @param string $on Условие для JOIN.
     * @return $this
     */
    public function leftJoin(string $table, string $on): self;

    /**
     * Добавляет JOIN указанного типа к запросу.
     *
     * @param string $type Тип JOIN (INNER, LEFT, RIGHT).
     * @param string $table Таблица для присоединения.
     * @param string|null $alias Псевдоним таблицы.
     * @param string $on Условие для JOIN.
     * @return $this
     */
    public function addJoin(string $type, string $table, ?string $alias, string $on): self;

    /**
     * Добавляет сортировку к запросу.
     *
     * @param string $column Столбец для сортировки.
     * @param string $direction Направление сортировки (ASC или DESC).
     * @return $this
     */
    public function orderBy(string $column, string $direction = 'ASC'): self;

    /**
     * Устанавливает лимит для запроса.
     *
     * @param int $limit Максимальное количество записей.
     * @return $this
     */
    public function limit(int $limit): self;

    /**
     * Устанавливает смещение для запроса.
     *
     * @param int $offset Количество записей для пропуска.
     * @return $this
     */
    public function offset(int $offset): self;

    /**
     * Выполняет запрос и возвращает массив объектов.
     *
     * @return array<object>
     */
    public function get(): array;

    /**
     * Выполняет запрос и возвращает первую запись в виде объекта.
     *
     * @return object|null
     */
    public function first(): ?object;

    /**
     * Выполняет запрос и возвращает значение одного столбца из первой записи.
     *
     * @param string $column Имя столбца.
     * @return mixed|null
     */
    public function value(string $column): mixed;

    /**
     * Выполняет запрос для подсчета количества записей.
     *
     * @param string $column Столбец для подсчета (обычно '*').
     * @return int
     */
    public function count(string $column = '*'): int;

    /**
     * Собирает и возвращает SQL-строку и массив параметров для подготовленного запроса.
     *
     * @return array{0: string, 1: array}
     */
    public function buildQuery(): array;

    /**
     * Выполняет INSERT-запрос.
     *
     * @param array<string, mixed> $data
     * @return int|false
     */
    public function insert(array $data): int|false;

    /**
     * Выполняет UPDATE-запрос на основе ранее установленных условий WHERE.
     *
     * @param array<string, mixed> $data
     * @return int|false
     */
    public function update(array $data): int|false;

    /**
     * Выполняет DELETE-запрос на основе ранее установленных условий WHERE.
     *
     * @return int|false
     */
    public function delete(): int|false;

    /**
     * Возвращает полное имя таблицы с префиксом WordPress.
     *
     * @param string $tableName Базовое имя таблицы.
     * @return string
     */
    public function getTableName(string $tableName): string;

    /**
     * Выполняет "сырой" SQL-запрос и возвращает первую запись.
     *
     * @param string $query SQL-запрос с плейсхолдерами (%s, %d).
     * @param mixed ...$args Параметры для запроса.
     * @return object|null
     */
    public function firstRaw(string $query, ...$args): ?object;
}