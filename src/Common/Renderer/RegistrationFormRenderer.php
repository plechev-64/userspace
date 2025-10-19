<?php

namespace UserSpace\Common\Renderer;

use UserSpace\Common\Service\TemplateManager;
use UserSpace\Common\Module\Form\Src\Infrastructure\FormFactory;
use UserSpace\Common\Module\Form\Src\Infrastructure\FormManager;

// Защита от прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RegistrationFormRenderer {

	public function __construct(
		private readonly FormManager $formManager,
		private readonly FormFactory $formFactory,
		private readonly TemplateManager $templateManager
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

		return $this->templateManager->render('registration_form', [
			'form' => $form,
			'settings' => $settings,
		]);
	}
}