<?php

require_once 'classes/Table.php';

if ( usp_is_ajax() ) {
	usp_table_scripts();
} else if ( is_admin() ) {
	add_action( 'admin_enqueue_scripts', 'usp_table_scripts', 10 );
} else {
	add_action( 'usp_enqueue_scripts', 'usp_table_scripts', 10 );
}
function usp_table_scripts() {
	wp_enqueue_style( 'usp-table', USP_URL . 'src/Module/table/assets/css/usp-table.css' );
	wp_enqueue_script( 'usp-table', USP_URL . 'src/Module/table/assets/js/usp-table.js' );
}
