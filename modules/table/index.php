<?php

require_once 'classes/class-usp-table.php';

if ( is_admin() || isset( $_REQUEST['rest_route'] ) ) {
    add_action( 'admin_enqueue_scripts', 'usp_table_scripts', 10 );
} else {
    add_action( 'usp_enqueue_scripts', 'usp_table_scripts', 10 );
}
function usp_table_scripts() {
    usp_enqueue_style( 'usp-table', USP_URL . 'modules/table/assets/css/usp-table.css', false, false, true );
    usp_enqueue_script( 'usp-table', USP_URL . 'modules/table/assets/js/usp-table.js', false, false, true );
}
