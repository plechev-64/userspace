<?php

namespace USP\Core\Module\Forms;

class Initializer {

	public function init(): void {
		if ( usp_is_ajax() ) {
			$this->usp_forms_scripts();
		} else if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', [$this, 'usp_forms_scripts'], 10 );
		} else {
			add_action( 'usp_enqueue_scripts', [$this, 'usp_forms_scripts'], 10 );
		}
	}

	public function usp_forms_scripts(): void {
		wp_enqueue_script( 'usp-forms', USP_URL . 'assets/modules/usp-forms.js' );
	}

}