<?php

namespace UserSpace\Core\Database;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Конструктор SQL-запросов для безопасного взаимодействия с базой данных WordPress.
 */
class QueryBuilder implements QueryBuilderInterface
{

    private readonly DatabaseConnectionInterface $db;

    private array $select = ['*'];
    private ?string $fromAlias = null;
    private ?string $from = null;
    private array $joins = [];
    private array $where = [];
    private array $orderBy = [];
    private ?int $limit = null;
    private ?int $offset = null;

    /**
     * @param DatabaseConnectionInterface $db Адаптер для работы с базой данных.
     */
    public function __construct(DatabaseConnectionInterface $db)
    {
        $this->db = $db;
    }

    public function getConnection(): DatabaseConnectionInterface
    {
        return $this->db;
    }

    /**
     * Устанавливает выбираемые столбцы.
     *
     * @param string|string[] $columns Столбцы для выбора.
     *
     * @return $this
     */
    public function select(array|string $columns = ['*']): self
    {
        $this->select = is_array($columns) ? $columns : func_get_args();

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
    public function from(string $table, ?string $alias = null): self
    {
        $this->from = $this->db->getTableName($table);
        $this->fromAlias = $alias;

        return $this;
    }

    /**
     * Устанавливает основную таблицу. Алиас для from().
     *
     * @param string $table Имя таблицы (без префикса).
     * @param string|null $alias Псевдоним таблицы.
     *
     * @return $this
     */
    public function table(string $table, ?string $alias = null): self
    {
        return $this->from($table, $alias);
    }

    /**
     * Добавляет условие WHERE.
     *
     * @param string $column Столбец.
     * @param string $operator Оператор сравнения (=, !=, >, <, IN, и т.д.).
     * @param mixed $value Значение для сравнения.
     *
     * @return $this
     */
    public function where(string|callable $column, ?string $operator = null, mixed $value = null): self
    {
        if (is_callable($column)) {
            $query = new self($this->db);
            $column($query);
            $this->where[] = ['type' => 'AND', 'condition' => $query];
            return $this;
        }

        $this->where[] = ['type' => 'AND', 'condition' => [
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
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
        $this->where[] = ['type' => 'OR', 'condition' => [
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
        ]];

        return $this;
    }

    /**
     * Добавляет LEFT JOIN.
     *
     * @param string $table Таблица для присоединения (без префикса).
     * @param string $on Условие для JOIN (например, 't1.id = t2.t1_id').
     *
     * @return $this
     */
    public function leftJoin(string $table, string $on): self
    {
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
        $table = $this->db->getTableName($table);
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
     * @param string $column Столбец для сортировки.
     * @param string $direction Направление (ASC или DESC).
     *
     * @return $this
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBy[] = $column . ' ' . ('DESC' === strtoupper($direction) ? 'DESC' : 'ASC');

        return $this;
    }

    /**
     * Устанавливает лимит записей.
     *
     * @param int $limit
     *
     * @return $this
     */
    public function limit(int $limit): self
    {
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
    public function offset(int $offset): self
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * Выполняет запрос и возвращает массив результатов.
     *
     * @return array
     */
    public function get(): array
    {
        list($query, $bindings) = $this->buildQuery();
        return $this->db->getResults($query, ...$bindings);
    }

    /**
     * Выполняет запрос и возвращает одну строку.
     *
     * @return object|null
     */
    public function first(): ?object
    {
        list($query, $bindings) = $this->buildQuery();

        return $this->db->getRow($query, ...$bindings);
    }

    /**
     * Выполняет запрос и возвращает одно значение.
     *
     * @return mixed|null
     */
    public function value(string $column): mixed
    {
        $this->select($column);
        list($query, $bindings) = $this->buildQuery();

        return $this->db->getVar($query, ...$bindings);
    }

    /**
     * Выполняет запрос COUNT(*) и возвращает количество строк.
     *
     * @param string $column
     * @return int
     */
    public function count(string $column = '*'): int
    {
        return (int)$this->value("COUNT({$column})");
    }

    /**
     * Собирает SQL-запрос и массив значений для подстановки.
     *
     * @return array
     */
    public function buildQuery(): array
    {
        $query = 'SELECT ' . implode(', ', $this->select);
        $query .= ' FROM ' . $this->from . ($this->fromAlias ? ' AS ' . $this->fromAlias : '');
        $bindings = [];

        if (!empty($this->joins)) {
            $query .= ' ' . implode(' ', $this->joins);
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

        if (!empty($this->orderBy)) {
            $query .= ' ORDER BY ' . implode(', ', $this->orderBy);
        }

        if (null !== $this->limit) {
            $query .= ' LIMIT %d';
            $bindings[] = $this->limit;
        }

        if (null !== $this->offset) {
            $query .= ' OFFSET %d';
            $bindings[] = $this->offset;
        }

        return [$query, $bindings];
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

    /**
     * Выполняет операцию INSERT.
     *
     * @param array $data Ассоциативный массив данных для вставки (column => value).
     * @return int|false Количество вставленных строк или false в случае ошибки.
     */
    public function insert(array $data): int|false
    {
        if (empty($this->from)) {
            return false;
        }

        $result = $this->db->insert($this->from, $data);
        $this->reset();

        return $result;
    }

    /**
     * Выполняет операцию UPDATE.
     *
     * @param array $data Ассоциативный массив данных для обновления (column => value).
     * @return int|false Количество обновленных строк или false в случае ошибки.
     */
    public function update(array $data): int|false
    {
        if (empty($this->from) || empty($this->where)) {
            return false;
        }

        list($whereClause, $bindings) = $this->buildWhere();
        $fullQuery = "UPDATE {$this->from} SET " .
            implode(', ', array_map(fn($col) => "`{$col}` = %s", array_keys($data))) .
            " WHERE " . $whereClause;

        $bindings = array_merge(array_values($data), $bindings);

        $result = $this->db->query($fullQuery, ...$bindings);
        $this->reset();

        return $result;
    }

    /**
     * Выполняет операцию DELETE.
     *
     * @return int|false Количество удаленных строк или false в случае ошибки.
     */
    public function delete(): int|false
    {
        if (empty($this->from) || empty($this->where)) {
            return false;
        }

        list($whereClause, $bindings) = $this->buildWhere();
        $fullQuery = "DELETE FROM {$this->from} WHERE " . $whereClause;

        $result = $this->db->query($fullQuery, ...$bindings);
        $this->reset();

        return $result;
    }

    /**
     * Сбрасывает состояние конструктора для нового запроса.
     */
    private function reset(): void
    {
        $this->select = ['*'];
        $this->from = null;
        $this->fromAlias = null;
        $this->joins = [];
        $this->where = [];
        $this->orderBy = [];
        $this->limit = null;
        $this->offset = null;
    }

    /**
     * Возвращает полное имя таблицы с префиксом WordPress.
     *
     * @param string $tableName Имя таблицы без префикса.
     * @return string
     */
    public function getTableName(string $tableName): string
    {
        return $this->db->getTableName($tableName);
    }

    /**
     * Выполняет "сырой" SQL-запрос и возвращает первую строку результата.
     *
     * @param string $query SQL-запрос с плейсхолдерами %s, %d, %f.
     * @param mixed ...$args Аргументы для подстановки в запрос.
     * @return object|null
     */
    public function firstRaw(string $query, ...$args): ?object
    {
        $this->reset(); // Сбрасываем состояние билдера, так как выполняется сырой запрос

        return $this->db->getRow($query, ...$args);
    }
}