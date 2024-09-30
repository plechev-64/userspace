<?php

namespace USP\Core\Module\FieldsManager;

class Initializer {

	public function init(): void {
		if ( usp_is_ajax() ) {
			$this->usp_fields_scripts();
		} else if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', [$this, 'usp_fields_scripts'], 10 );
		} else {
			add_action( 'usp_enqueue_scripts', [$this, 'usp_fields_scripts'], 10 );
		}

		add_filter( 'usp_field_options', [$this, 'usp_edit_field_options'], 10, 3 );

		require_once 'ajax.php';

	}

	public function usp_fields_scripts() {
		wp_enqueue_style( 'usp-fields-manager', USP_URL . 'assets/modules/fields-manager/usp-fields-manager.css' );
		wp_enqueue_script( 'usp-fields-manager', USP_URL . 'assets/modules/fields-manager/usp-fields-manager.js' );
	}

}