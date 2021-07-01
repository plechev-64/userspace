<?php

require_once 'classes/class-usp-option.php';
require_once 'classes/class-usp-options-box.php';
require_once 'classes/class-usp-options-group.php';
require_once 'classes/class-usp-options-manager.php';
require_once 'functions.php';

if ( is_admin() || isset( $_REQUEST['rest_route'] ) ) {
    usp_options_manager_scripts();
} else {
    add_action( 'usp_enqueue_scripts', 'usp_options_manager_scripts', 10 );
}
function usp_options_manager_scripts() {
    usp_enqueue_style( 'usp-options-manager', USP_URL . 'modules/options-manager/assets/css/usp-options-manager.css' );
    usp_enqueue_script( 'usp-options-manager', USP_URL . 'modules/options-manager/assets/js/usp-options-manager.js' );
}
