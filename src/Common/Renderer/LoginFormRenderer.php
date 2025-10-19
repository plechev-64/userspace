<?php

namespace UserSpace\Common\Renderer;

use UserSpace\Common\Service\TemplateManager;

// Защита от прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LoginFormRenderer
{
    public function __construct(private readonly TemplateManager $templateManager)
    {
    }

	public function render(): string {
		if ( is_user_logged_in() ) {
			return '<p>' . __( 'You are already logged in.', 'usp' ) . '</p>';
		}

		wp_enqueue_style( 'usp-form' );
		wp_enqueue_script( 'usp-login-handler' );

		return $this->templateManager->render('login_form');
	}
}