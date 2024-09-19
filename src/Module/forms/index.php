<?php

require_once 'classes/class-usp-form.php';

if ( usp_is_ajax() ) {
	usp_forms_scripts();
} else if ( is_admin() ) {
	add_action( 'admin_enqueue_scripts', 'usp_forms_scripts', 10 );
} else {
	add_action( 'usp_enqueue_scripts', 'usp_forms_scripts', 10 );
}
function usp_forms_scripts() {
	usp_enqueue_script( 'usp-forms', USP_URL . 'src/Module/forms/assets/js/usp-forms.js', false, false, true );
}
