<?php

namespace UserSpace\Core\Queue;

// Защита от прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Отправляет задачи в очередь.
 */
class QueueDispatcher {

	/**
	 * Добавляет задачу в очередь.
	 *
	 * @param QueueableMessage $message Объект сообщения для постановки в очередь.
	 * @param int $delay_seconds Задержка перед выполнением в секундах.
	 *
	 * @return bool|int
	 */
	public function dispatch( QueueableMessage $message, int $delay_seconds = 0 ): bool|int {
		global $wpdb;
		$table_name = $wpdb->prefix . 'userspace_jobs';

		$available_at = gmdate( 'Y-m-d H:i:s', time() + $delay_seconds );

		return $wpdb->insert( $table_name, [
				'message_class'      => get_class( $message ),
				'args'         => serialize( $message->toArray() ),
				'available_at' => $available_at,
				'created_at'   => gmdate( 'Y-m-d H:i:s' ),
			]
		);
	}
}