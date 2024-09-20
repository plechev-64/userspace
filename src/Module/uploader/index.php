<?php

require_once 'Uploader.php';

if ( usp_is_ajax() ) {
	usp_uploader_scripts();
} else if ( is_admin() ) {
	add_action( 'admin_enqueue_scripts', 'usp_uploader_scripts', 10 );
} else {
	add_action( 'usp_enqueue_scripts', 'usp_uploader_scripts', 10 );
}
function usp_uploader_scripts() {
	usp_enqueue_style( 'usp-uploader', USP_URL . 'src/Module/uploader/assets/css/usp-uploader.css', false, false, true );
	usp_enqueue_script( 'usp-uploader', USP_URL . 'src/Module/uploader/assets/js/usp-uploader.js', false, false, true );
}
