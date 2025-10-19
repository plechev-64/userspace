<?php

namespace UserSpace\Common\Module\Queue\Src\Domain;

// Защита от прямого доступа к файлу

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Интерфейс для всех обработчиков задач из очереди.
 */
interface MessageHandler
{
    public function handle(QueueableMessage $message): void;
}