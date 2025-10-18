<?php

namespace UserSpace\Core\Database;

use wpdb;

// Защита от прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Конструктор SQL-запросов для безопасного взаимодействия с базой данных WordPress.
 */
class QueryBuilder {

    private readonly wpdb $wpdb;

    private array $select = [ '*' ];
    private ?string $fromAlias = null;
    private ?string $from = null;
    private array $joins = [];
    private array $where = [];
    private array $orderBy = [];
    private ?int $limit = null;
    private ?int $offset = null;

    /**
     * @param wpdb $wpdb Экземпляр класса wpdb.
     */
    public function __construct( wpdb $wpdb ) {
        $this->wpdb = $wpdb;
    }

    public function getWpdb(): wpdb
    {
        return $this->wpdb;
    }

    /**
     * Устанавливает выбираемые столбцы.
     *
     * @param string|string[] $columns Столбцы для выбора.
     *
     * @return $this
     */
    public function select( $columns = [ '*' ] ): self {
        $this->select = is_array( $columns ) ? $columns : func_get_args();

        return $this;
    }

    /**
     * Устанавливает основную таблицу.
     *
     * @param string $table Имя таблицы (без префикса).
     * @param string|null $alias Псевдоним таблицы.
     *
     * @return $this
     */
    public function from( string $table, ?string $alias = null ): self {
        if (strpos($table, $this->wpdb->prefix) !== 0) {
            $this->from = $this->wpdb->prefix . $table;
        } else {
            $this->from = $table;
        }
        $this->fromAlias = $alias;

        return $this;
    }

    /**
     * Добавляет условие WHERE.
     *
     * @param string $column   Столбец.
     * @param string $operator Оператор сравнения (=, !=, >, <, IN, и т.д.).
     * @param mixed  $value    Значение для сравнения.
     *
     * @return $this
     */
    public function where( string|callable $column, ?string $operator = null, mixed $value = null ): self {
        if (is_callable($column)) {
            $query = new self($this->wpdb);
            $column($query);
            $this->where[] = [ 'type' => 'AND', 'condition' => $query ];
            return $this;
        }

        $this->where[] = [ 'type' => 'AND', 'condition' => [
            'column'   => $column,
            'operator' => $operator,
            'value'    => $value,
        ]];


        return $this;
    }

    /**
     * Добавляет условие OR WHERE.
     *
     * @param string|callable $column
     * @param string|null $operator
     * @param mixed|null $value
     * @return $this
     */
    public function orWhere(string|callable $column, ?string $operator = null, mixed $value = null): self
    {
        // Логика для orWhere идентична where, но с другим типом соединения
        $this->where[] = [ 'type' => 'OR', 'condition' => [
            'column'   => $column,
            'operator' => $operator,
            'value'    => $value,
        ]];

        return $this;
    }

    /**
     * Добавляет LEFT JOIN.
     *
     * @param string $table Таблица для присоединения (без префикса).
     * @param string $on    Условие для JOIN (например, 't1.id = t2.t1_id').
     *
     * @return $this
     */
    public function leftJoin( string $table, string $on ): self {
        return $this->addJoin('LEFT', $table, null, $on);
    }

    /**
     * Добавляет JOIN.
     *
     * @param string $type Тип JOIN (LEFT, RIGHT, INNER).
     * @param string $table Таблица для присоединения.
     * @param string|null $alias Псевдоним таблицы.
     * @param string $on Условие для JOIN.
     *
     * @return $this
     */
    public function addJoin(string $type, string $table, ?string $alias, string $on): self
    {
        if (strpos($table, $this->wpdb->prefix) !== 0) {
            $table = $this->wpdb->prefix . $table;
        }

        $type = strtoupper($type);
        if (str_ends_with($type, 'JOIN')) {
            $joinString = $type . ' ' . $table;
        } else {
            $joinString = $type . ' JOIN ' . $table;
        }
        if ($alias) {
            $joinString .= ' AS ' . $alias;
        }
        $this->joins[] = $joinString . ' ON ' . $on;
        return $this;
    }

    /**
     * Устанавливает сортировку.
     *
     * @param string $column    Столбец для сортировки.
     * @param string $direction Направление (ASC или DESC).
     *
     * @return $this
     */
    public function orderBy( string $column, string $direction = 'ASC' ): self {
        $this->orderBy[] = $column . ' ' . ( 'DESC' === strtoupper( $direction ) ? 'DESC' : 'ASC' );

        return $this;
    }

    /**
     * Устанавливает лимит записей.
     *
     * @param int $limit
     *
     * @return $this
     */
    public function limit( int $limit ): self {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Устанавливает смещение.
     *
     * @param int $offset
     *
     * @return $this
     */
    public function offset( int $offset ): self {
        $this->offset = $offset;

        return $this;
    }

    /**
     * Выполняет запрос и возвращает массив результатов.
     *
     * @return array
     */
    public function get(): array {
        list( $query, $bindings ) = $this->buildQuery();
        return $this->wpdb->get_results( $this->wpdb->prepare( $query, $bindings ) );
    }

    /**
     * Выполняет запрос и возвращает одну строку.
     *
     * @return object|null
     */
    public function first(): ?object {
        list( $query, $bindings ) = $this->buildQuery();

        return $this->wpdb->get_row( $this->wpdb->prepare( $query, $bindings ) );
    }

    /**
     * Выполняет запрос и возвращает одно значение.
     *
     * @return mixed|null
     */
    public function value(string $column) {
        $this->select($column);
        list( $query, $bindings ) = $this->buildQuery();

        return $this->wpdb->get_var( $this->wpdb->prepare( $query, $bindings ) );
    }

    /**
     * Выполняет запрос COUNT(*) и возвращает количество строк.
     *
     * @param string $column
     * @return int
     */
    public function count(string $column = '*'): int
    {
        return (int) $this->value("COUNT({$column})");
    }

    /**
     * Собирает SQL-запрос и массив значений для подстановки.
     *
     * @return array
     */
    public function buildQuery(): array {
        $query    = 'SELECT ' . implode( ', ', $this->select );
        $query    .= ' FROM ' . $this->from . ($this->fromAlias ? ' AS ' . $this->fromAlias : '');
        $bindings = [];

        if ( ! empty( $this->joins ) ) {
            $query .= ' ' . implode( ' ', $this->joins );
        }

        if (!empty($this->where)) {
            $query .= ' WHERE ';
            $whereClauses = [];
            foreach ($this->where as $index => $whereItem) {
                $condition = $whereItem['condition'] ?? $whereItem;
                $type = $whereItem['type'];

                if ($index > 0) {
                    $whereClauses[] = $type;
                }

                if ($condition instanceof self) {
                    list($subQuery, $subBindings) = $condition->buildWhere();
                    // Если подзапрос не пустой, добавляем его в скобках
                    if (!empty($subQuery)) {
                        $whereClauses[] = '(' . $subQuery . ')';
                    }
                    $bindings = array_merge($bindings, $subBindings);
                } elseif (is_array($condition['value'])) { // IN (...)
                    $placeholders = implode(', ', array_fill(0, count($condition['value']), '%s'));
                    $whereClauses[] = $condition['column'] . ' ' . $condition['operator'] . ' (' . $placeholders . ')';
                    $bindings = array_merge($bindings, $condition['value']);
                } else { // Простое условие
                    $placeholder = '%s';
                    if (strtoupper($condition['operator']) === 'LIKE') {
                        $placeholder = '%s';
                    } elseif (is_int($condition['value'])) {
                        $placeholder = '%d';
                    } elseif (is_float($condition['value'])) {
                        $placeholder = '%f';
                    }
                    $whereClauses[] = $condition['column'] . ' ' . $condition['operator'] . ' ' . $placeholder;
                    $bindings[] = $condition['value'];
                }
            }
            $query .= implode(' ', $whereClauses);
        }

        if ( ! empty( $this->orderBy ) ) {
            $query .= ' ORDER BY ' . implode( ', ', $this->orderBy );
        }

        if ( null !== $this->limit ) {
            $query      .= ' LIMIT %d';
            $bindings[] = $this->limit;
        }

        if ( null !== $this->offset ) {
            $query      .= ' OFFSET %d';
            $bindings[] = $this->offset;
        }

        return [ $query, $bindings ];
    }

    /**
     * Собирает только часть WHERE запроса.
     *
     * @return array
     */
    private function buildWhere(): array
    {
        $bindings = [];
        $whereClauses = [];

        foreach ($this->where as $index => $whereItem) {
            $condition = $whereItem['condition'] ?? $whereItem;
            $type = $whereItem['type'];

            if ($index > 0) {
                $whereClauses[] = $type;
            }

            if ($condition instanceof self) {
                list($subQuery, $subBindings) = $condition->buildWhere();
                if (!empty($subQuery)) {
                    $whereClauses[] = '(' . $subQuery . ')';
                    $bindings = array_merge($bindings, $subBindings);
                }
            } elseif (is_array($condition['value'])) { // IN (...)
                $placeholders = implode(', ', array_fill(0, count($condition['value']), '%s'));
                $whereClauses[] = $condition['column'] . ' ' . $condition['operator'] . ' (' . $placeholders . ')';
                $bindings = array_merge($bindings, $condition['value']);
            } else { // Простое условие
                $placeholder = '%s';
                if (strtoupper($condition['operator']) === 'LIKE') {
                    $placeholder = '%s';
                } elseif (is_int($condition['value'])) {
                    $placeholder = '%d';
                } elseif (is_float($condition['value'])) {
                    $placeholder = '%f';
                }
                $whereClauses[] = $condition['column'] . ' ' . $condition['operator'] . ' ' . $placeholder;
                $bindings[] = $condition['value'];
            }
        }

        return [implode(' ', $whereClauses), $bindings];
    }
}