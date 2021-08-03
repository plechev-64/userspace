<?php

usp_ajax_action( 'usp_get_table_manager_cols', true, true );
function usp_get_table_manager_cols() {

	$manager_id    = $_POST['manager_id'];
	$cols          = $_POST['cols'];
	$active_cols   = $_POST['active_cols'];
	$disabled_cols = $_POST['disabled_cols'];

	$manager = new USP_Table_Cols_Manager( $manager_id, array(
		'cols'          => $cols,
		'active_cols'   => $active_cols,
		'disabled_cols' => $disabled_cols,
	) );

	return array(
		'dialog' => array(
			'size'    => 'medium',
			'title'   => __( 'Column manager', 'userspace' ),
			'content' => $manager->get_manager()
		)
	);
}

usp_ajax_action( 'usp_save_table_manager_cols', true, true );
function usp_save_table_manager_cols() {

	$manager_id = $_POST['manager_id'];
	$col_ids    = $_POST['col_ids'];

	setcookie( $manager_id, json_encode( $col_ids ), time() + 3600 * 24 * 30 * 12, '/', $_SERVER['HOST'] );

	return array(
		'success' => __( 'The structure of the table is saved!', 'userspace' ),
		'reload'  => true
	);
}

usp_ajax_action( 'usp_load_content_manager', true, true );
function usp_load_content_manager() {

	$class     = $_REQUEST['classname'];
	$classargs = $_POST['classargs'] ?? null;
	$tail      = $_POST['tail'] ?? null;

	if ( ! is_subclass_of( $class, 'USP_Content_Manager' ) ) {
		return array(
			'error' => __( 'Error', 'userspace' )
		);
	}

	$Manager = new $class( $classargs );

	return array(
		'content' => $Manager->get_manager_content()
	);
}

usp_ajax_action( 'usp_load_content_manager_state', true, true );
function usp_load_content_manager_state() {

	$class                   = $_REQUEST['state']['classname'];
	$classargs               = $_REQUEST['state']['classargs'] ?? null;
	$classargs['startstate'] = 0;

	if ( ! is_subclass_of( $class, 'USP_Content_Manager' ) ) {
		return array(
			'error' => __( 'Error', 'userspace' )
		);
	}

	$Manager = new $class( $classargs );

	return array(
		'content' => $Manager->get_manager_content()
	);
}
