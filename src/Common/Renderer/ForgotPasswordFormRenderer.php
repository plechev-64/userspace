<?php

namespace UserSpace\Common\Renderer;

use UserSpace\Common\Service\TemplateManager;

// Защита от прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ForgotPasswordFormRenderer
{
    public function __construct(private readonly TemplateManager $templateManager)
    {
    }

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

		return $this->templateManager->render('forgot_password_form');
	}
}