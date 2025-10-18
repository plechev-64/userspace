<?php

namespace UserSpace\Core\Queue;

// Защита от прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Интерфейс для всех обработчиков задач из очереди.
 */
interface MessageHandler {
	public function handle( QueueableMessage $message ): void;
}