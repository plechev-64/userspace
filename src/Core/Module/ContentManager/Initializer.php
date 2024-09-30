<?php

namespace USP\Core\Module\ContentManager;

class Initializer {

	public function init(): void {
		if ( usp_is_ajax() ) {
			$this->usp_content_manager_scripts();
		} else if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', [$this, 'usp_content_manager_scripts'], 10 );
		} else {
			add_action( 'usp_enqueue_scripts', [$this, 'usp_content_manager_scripts'], 10 );
		}

		require_once 'ajax.php';
	}

	public function usp_content_manager_scripts() {
		wp_enqueue_style( 'usp-content-manager', USP_URL . 'assets/modules/content-manager/usp-content-manager.css' );
		wp_enqueue_script( 'usp-content-manager', USP_URL . 'assets/modules/content-manager/usp-content-manager.js', [ 'usp-core-scripts' ]);
	}

}