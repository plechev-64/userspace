<?php

require_once 'classes/FieldsManager.php';
require_once 'functions.php';

if ( usp_is_ajax() ) {
	usp_fields_manager_scripts();
} else if ( is_admin() ) {
	add_action( 'admin_enqueue_scripts', 'usp_fields_manager_scripts', 10 );
} else {
	add_action( 'usp_enqueue_scripts', 'usp_fields_manager_scripts', 10 );
}

function usp_fields_manager_scripts() {
	usp_enqueue_style( 'usp-fields-manager', USP_URL . 'src/Module/fields-manager/assets/css/usp-fields-manager.css', false, false, true );
	usp_enqueue_script( 'usp-fields-manager', USP_URL . 'src/Module/fields-manager/assets/js/usp-fields-manager.js', false, false, true );
}
