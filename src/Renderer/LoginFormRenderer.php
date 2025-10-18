<?php

namespace UserSpace\Renderer;

// Защита от прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LoginFormRenderer {

	public function render(): string {
		if ( is_user_logged_in() ) {
			return '<p>' . __( 'You are already logged in.', 'usp' ) . '</p>';
		}

		wp_enqueue_style( 'usp-form' );
		wp_enqueue_script( 'usp-login-handler' );

		ob_start();
		include USERSPACE_PLUGIN_DIR . 'views/login-form-template.php';

		return ob_get_clean();
	}
}