<?php

require_once 'classes/class-usp-fields-manager.php';
require_once 'functions.php';
function usp_fields_manager_scripts() {
	usp_enqueue_style( 'usp-fields-manager', USP_URL . 'modules/fields-manager/style.css', false, false, true );
	usp_enqueue_script( 'usp-fields-manager', USP_URL . 'modules/fields-manager/scripts.js', false, false, true );
}

if ( is_admin() || isset( $_REQUEST['rest_route'] ) ) {
	usp_fields_manager_scripts();
} else {
	add_action( 'usp_enqueue_scripts', 'usp_fields_manager_scripts', 10 );
}
