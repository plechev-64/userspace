<?php

namespace UserSpace\Core\Query;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Интерфейс для взаимодействия с глобальным запросом WordPress.
 */
interface QueryApiInterface
{
    /**
     * Получает значение переменной запроса.
     * Обертка для get_query_var().
     *
     * @param string $varName Имя переменной.
     * @param mixed $default Значение по умолчанию.
     * @return mixed
     */
    public function getQueryVar(string $varName, mixed $default = ''): mixed;
}