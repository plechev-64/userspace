<?php

require_once 'classes/class-usp-content-manager.php';
require_once 'classes/class-usp-table-manager.php';
require_once 'classes/class-usp-table-cols-manager.php';
require_once 'functions-ajax.php';

if ( usp_is_ajax() ) {
	usp_content_manager_scripts();
} else if ( is_admin() ) {
	add_action( 'admin_enqueue_scripts', 'usp_content_manager_scripts', 10 );
} else {
	add_action( 'usp_enqueue_scripts', 'usp_content_manager_scripts', 10 );
}
function usp_content_manager_scripts() {
	usp_enqueue_style( 'usp-content-manager', USP_URL . 'modules/content-manager/assets/css/usp-content-manager.css', false, false, true );
	usp_enqueue_script( 'usp-content-manager', USP_URL . 'modules/content-manager/assets/js/usp-content-manager.js', [ 'usp-core-scripts' ], false, true );
}
