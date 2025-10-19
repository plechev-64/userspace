<?php

namespace UserSpace\Module\Queue\App\Task\Message;

use UserSpace\Module\Queue\Src\Domain\AbstractMessage;

// Защита от прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Тестовое сообщение для проверки работоспособности очереди.
 */
class PingMessage extends AbstractMessage {
	public function __construct(
		public readonly int $sentAt = 0
	) {}
}