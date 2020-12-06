<?php

require_once 'classes/class-usp-form.php';
function usp_forms_scripts() {
	usp_enqueue_style( 'usp-forms', USP_URL . 'modules/forms/style.css', false, false, true );
	usp_enqueue_script( 'usp-forms', USP_URL . 'modules/forms/scripts.js', false, false, true );
}

if ( is_admin() || isset( $_REQUEST['rest_route'] ) ) {
	usp_forms_scripts();
} else {
	add_action( 'usp_enqueue_scripts', 'usp_forms_scripts', 10 );
}
