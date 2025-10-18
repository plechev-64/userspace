<?php

namespace UserSpace\JobHandler;

use UserSpace\Core\Queue\MessageHandler;
use UserSpace\Core\Queue\QueueableMessage;

// Защита от прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PingHandler implements MessageHandler {

    /**
     * @param QueueableMessage $message
     */
	public function handle( QueueableMessage $message ): void {
		// Имитируем полезную работу
		sleep(2);
	}
}