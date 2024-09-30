<?php

namespace USP\Core\Module\Uploader;

class Initializer {

	public function init(): void {
		if ( usp_is_ajax() ) {
			$this->usp_uploader_scripts();
		} else if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', [$this, 'usp_uploader_scripts'], 10 );
		} else {
			add_action( 'usp_enqueue_scripts', [$this, 'usp_uploader_scripts'], 10 );
		}
	}

	public function usp_uploader_scripts() {
		wp_enqueue_style( 'usp-uploader', USP_URL . 'assets/modules/uploader/usp-uploader.css' );
		wp_enqueue_script( 'usp-uploader', USP_URL . 'assets/modules/uploader/usp-uploader.js' );
	}

}