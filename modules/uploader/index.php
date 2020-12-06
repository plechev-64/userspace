<?php

require_once 'class-usp-uploader.php';
function usp_uploader_scripts() {
	usp_enqueue_style( 'usp-uploader', USP_URL . 'modules/uploader/style.css', false, false, true );
	usp_enqueue_script( 'usp-uploader', USP_URL . 'modules/uploader/scripts.js', false, false, true );
}

if ( is_admin() || isset( $_REQUEST['rest_route'] ) ) {
	usp_uploader_scripts();
} else {
	add_action( 'usp_enqueue_scripts', 'usp_uploader_scripts', 10 );
}
