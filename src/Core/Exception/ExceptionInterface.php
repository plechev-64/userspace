<?php

namespace UserSpace\Core\Exception;

/**
 * Интерфейс для адаптера над классом WP_Error.
 */
interface ExceptionInterface
{
    /**
     * Возвращает дополнительные данные об ошибке.
     */
    public function getData(): mixed;
}