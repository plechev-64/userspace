<?php

namespace USP\Core\Module\Fields;

class Initializer {

	public function init(): void {
		if ( usp_is_ajax() ) {
			$this->usp_fields_scripts();
		} else if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', [$this, 'usp_fields_scripts'], 10 );
		} else {
			add_action( 'usp_enqueue_scripts', [$this, 'usp_fields_scripts'], 10 );
		}
	}

	public function usp_fields_scripts() {
		wp_enqueue_style( 'usp-fields', USP_URL . 'assets/modules/fields/usp-fields.css' );
		wp_enqueue_script( 'usp-fields', USP_URL . 'assets/modules/fields/usp-fields.js', [ 'usp-core-scripts' ] );
	}

}