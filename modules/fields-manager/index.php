<?php

require_once 'classes/class-usp-fields-manager.php';
require_once 'functions.php';

if ( is_admin() || isset( $_REQUEST['rest_route'] ) ) {
    add_action( 'admin_enqueue_scripts', 'usp_fields_manager_scripts', 10 );
} else {
    add_action( 'usp_enqueue_scripts', 'usp_fields_manager_scripts', 10 );
}
function usp_fields_manager_scripts() {
    usp_enqueue_style( 'usp-fields-manager', USP_URL . 'modules/fields-manager/assets/css/usp-fields-manager.css', false, false, true );
    usp_enqueue_script( 'usp-fields-manager', USP_URL . 'modules/fields-manager/assets/js/usp-fields-manager.js', false, false, true );
}
