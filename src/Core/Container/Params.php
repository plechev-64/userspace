<?php

namespace UserSpace\Core\Container;

/**
 * Простой класс-обертка для работы с массивом параметров,
 * когда необходимо по ходу загрузки конфигураций данный массив дополнять
 */
class Params
{
    /**
     * @var array<string, mixed>
     */
    private array $params = [];

    /**
     * @param array<string, mixed> $params
     */
    public function __construct(array $params = [])
    {
        $this->params = $params;
    }

    public function set(string $key, mixed $value): void
    {
        $this->params[$key] = $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->params[$key] ?? $default;
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->params;
    }
}