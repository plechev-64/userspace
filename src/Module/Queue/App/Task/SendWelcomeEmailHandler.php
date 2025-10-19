<?php

namespace UserSpace\Module\Queue\App\Task;

use UserSpace\Module\Queue\Src\Domain\MessageHandler;
use UserSpace\Module\Queue\Src\Domain\QueueableMessage;

// Защита от прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SendWelcomeEmailHandler implements MessageHandler {

    /**
     * @param QueueableMessage $message
     */
	public function handle( QueueableMessage $message ): void {
		$user = get_userdata( $message->userId );

		if ( $user ) {
			// Здесь может быть сложная логика: генерация письма из шаблона, логирование и т.д.
			// Для примера просто используем wp_mail.
			$subject = 'Добро пожаловать на наш сайт!';
			$messageText = "Здравствуйте, {$user->display_name}! Спасибо за регистрацию. Вы выбрали шаблон: {$message->templateName}.";

			// Эта операция может быть долгой, но теперь она не замедляет работу сайта.
			wp_mail( $user->user_email, $subject, $messageText );
		}
	}
}