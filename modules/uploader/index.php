<?php

require_once 'class-usp-uploader.php';

if ( is_admin() || isset( $_REQUEST['rest_route'] ) ) {
	add_action( 'admin_enqueue_scripts', 'usp_uploader_scripts', 10 );
} else {
	add_action( 'usp_enqueue_scripts', 'usp_uploader_scripts', 10 );
}
function usp_uploader_scripts() {
	usp_enqueue_style( 'usp-uploader', USP_URL . 'modules/uploader/assets/css/usp-uploader.css', false, false, true );
	usp_enqueue_script( 'usp-uploader', USP_URL . 'modules/uploader/assets/js/usp-uploader.js', false, false, true );
}
