<?php

namespace UserSpace\Renderer;

use UserSpace\Module\Form\Src\Infrastructure\FormFactory;
use UserSpace\Module\Form\Src\Infrastructure\FormManager;

// Защита от прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RegistrationFormRenderer {

	public function __construct(
		private readonly FormManager $formManager,
		private readonly FormFactory $formFactory
	) {
	}

	public function render(): string {
		if ( is_user_logged_in() ) {
			return '<p>' . __( 'You are already registered and logged in.', 'usp' ) . '</p>';
		}

		$formType = 'registration';
		$config   = $this->formManager->load( $formType );

		if ( null === $config ) {
			return '<p style="color: red;">' . __( 'Registration form is not configured yet.', 'usp' ) . '</p>';
		}

		wp_enqueue_style( 'usp-form' );
		wp_enqueue_script( 'usp-registration-handler' );

		// $config уже является DTO, передаем его напрямую в фабрику
		$form     = $this->formFactory->create( $config );
		$settings = get_option( 'usp_settings', [] );

		ob_start();
		include USERSPACE_PLUGIN_DIR . 'views/registration-form-template.php';

		return ob_get_clean();
	}
}