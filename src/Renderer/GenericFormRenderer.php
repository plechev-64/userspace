<?php

namespace UserSpace\Renderer;

use UserSpace\Form\FormFactory;
use UserSpace\Form\FormManager;

// Защита от прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GenericFormRenderer {

	public function __construct(
		private readonly FormManager $formManager,
		private readonly FormFactory $formFactory
	) {
	}

	public function render( array $atts ): string {
		$form_type = sanitize_key( $atts['type'] ?? '' );

		if ( empty( $form_type ) ) {
			return '<p style="color: red;">' . __( 'Error: "type" attribute not specified in the shortcode.', 'usp' ) . '</p>';
		}

		$config = $this->formManager->load( $form_type );

		if ( null === $config ) {
			return sprintf( '<p style="color: red;">' . __( 'Error: form with type "%s" not found.', 'usp' ) . '</p>', esc_html( $form_type ) );
		}

		wp_enqueue_style( 'usp-form' );
		wp_enqueue_script( 'usp-form-handler' );
		wp_localize_script(
			'usp-form-handler',
			'uspL10n',
			[
				'formHandler' => [
					'saving' => __( 'Saving...', 'usp' ),
				],
			]
		);

		if ( ! empty( $_POST ) ) {
			foreach ( $config['sections'] as &$section ) {
				foreach ( $section['blocks'] as &$block ) {
					foreach ( $block['fields'] as $name => &$field_config ) {
						if ( isset( $_POST[ $name ] ) ) {
							$field_config['value'] = sanitize_text_field( wp_unslash( $_POST[ $name ] ) );
						}
					}
				}
			}
		}

		$form = $this->formFactory->create( $config );

		ob_start();
		echo '<form method="post" class="usp-form" data-usp-form data-usp-action="' . esc_attr( $atts['action'] ?? '' ) . '">';
		echo $form->render();
		echo '<div class="usp-form-submit-wrapper"><button type="submit">' . __( 'Save', 'usp' ) . '</button></div>';
		echo '</form>';

		return ob_get_clean();
	}
}