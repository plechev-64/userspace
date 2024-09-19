<?php

usp_ajax_action( 'usp_get_table_manager_cols', true, true );
function usp_get_table_manager_cols() {

	$manager_id    = $_POST['manager_id'];
	$cols          = $_POST['cols'];
	$active_cols   = $_POST['active_cols'];
	$disabled_cols = $_POST['disabled_cols'];

	$manager = new TableColsManager( $manager_id, [
		'cols'          => $cols,
		'active_cols'   => $active_cols,
		'disabled_cols' => $disabled_cols,
	] );

	return [
		'dialog' => [
			'size'    => 'medium',
			'title'   => __( 'Column manager', 'userspace' ),
			'content' => $manager->get_manager()
		]
	];
}

usp_ajax_action( 'usp_save_table_manager_cols', true, true );
function usp_save_table_manager_cols() {

	$manager_id = $_POST['manager_id'];
	$col_ids    = $_POST['col_ids'];

	setcookie( $manager_id, json_encode( $col_ids ), time() + 3600 * 24 * 30 * 12, '/', $_SERVER['HOST'] );

	return [
		'success' => __( 'The structure of the table is saved!', 'userspace' ),
		'reload'  => true
	];
}

usp_ajax_action( 'usp_load_content_manager', true, true );
function usp_load_content_manager() {

	$class      = ! empty( $_POST['classname'] ) ? sanitize_text_field( wp_unslash( $_POST['classname'] ) ) : '';
	$startstate = ! empty( $_POST['startstate'] ) ? json_decode( wp_unslash( $_POST['startstate'] ), true ) : [];

	if ( ! $class || ! is_subclass_of( $class, 'ContentManager' ) ) {
		return [
			'error' => __( 'Error', 'userspace' )
		];
	}

	/**
	 * @var ContentManager $Manager
	 */

	$Manager = new $class( $startstate );

	if ( $Manager->has_error() ) {
		return [ 'error' => $Manager->get_error()->get_error_message() ];
	}

	return [ 'content' => $Manager->get_content_body() ];
}
