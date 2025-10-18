<?php

namespace UserSpace\Core\Queue;

// Защита от прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Интерфейс для всех сообщений, которые можно поставить в очередь.
 */
interface QueueableMessage {
	public function toArray(): array;

	public static function fromArray( array $data ): static;
}