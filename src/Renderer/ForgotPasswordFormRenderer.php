<?php

namespace UserSpace\Renderer;

// Защита от прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ForgotPasswordFormRenderer {

	public function render(): string {
		if ( is_user_logged_in() ) {
			return ''; // Ничего не показываем авторизованным пользователям
		}

		wp_enqueue_style( 'usp-form' );
		wp_enqueue_script( 'usp-forgot-password-handler' );
		wp_localize_script(
			'usp-forgot-password-handler',
			'uspL10n',
			[
				'forgotPassword' => [
					'processing' => __( 'Processing...', 'usp' ),
				],
			]
		);

		ob_start();
		include USERSPACE_PLUGIN_DIR . 'views/forgot-password-form-template.php';

		return ob_get_clean();
	}
}