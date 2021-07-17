<?php

require_once 'classes/class-usp-wizard-step.php';
require_once 'classes/class-usp-wizard.php';

if ( is_admin() || isset( $_REQUEST['rest_route'] ) ) {
	usp_wizard_scripts();
} else {
	add_action( 'usp_enqueue_scripts', 'usp_wizard_scripts', 10 );
}
function usp_wizard_scripts() {
	usp_enqueue_style( 'usp-wizard', USP_URL . 'modules/wizard/assets/css/usp-wizard.css', false, false, true );
}
