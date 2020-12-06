<?php

require_once 'classes/class-usp-table.php';
function usp_table_scripts() {
	usp_enqueue_style( 'usp-table', USP_URL . 'modules/table/style.css', false, false, true );
	usp_enqueue_script( 'usp-table', USP_URL . 'modules/table/scripts.js', false, false, true );
}

if ( is_admin() || isset( $_REQUEST['rest_route'] ) ) {
	usp_table_scripts();
} else {
	add_action( 'usp_enqueue_scripts', 'usp_table_scripts', 10 );
}
