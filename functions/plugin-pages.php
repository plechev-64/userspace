<?php

/**
 * Check if isset plugin page.
 *
 * @param   $page_id    int ID page
 *
 * @return  bool
 *
 * @since   1.0.0
 */
function usp_isset_plugin_page( $page_id ) {
	return usp_get_plugin_page( $page_id ) ? true : false;
}

function usp_create_plugin_page( $page_id, $args ) {
	$insert_id = wp_insert_post( wp_parse_args( $args, [
		'post_status' => 'publish',
		'post_author' => get_current_user_id(),
		'post_type'   => 'page'
	] ) );

	if ( ! $insert_id ) {
		return false;
	}

	$plugin_pages = get_site_option( 'usp_plugin_pages' );

	$plugin_pages[ $page_id ] = $insert_id;

	update_site_option( 'usp_plugin_pages', $plugin_pages );

	return $insert_id;
}

function usp_create_plugin_page_if_need( $page_id, $args ) {
	if ( ! usp_isset_plugin_page( $page_id ) ) {
		return usp_create_plugin_page( $page_id, $args );
	}

	return false;
}

function usp_get_plugin_page( $page_id ) {
	$plugin_pages = get_site_option( 'usp_plugin_pages' );

	if ( ! isset( $plugin_pages[ $page_id ] ) ) {
		return false;
	}

	return ( new USP_Posts_Query() )
		->select( 'ID' )
		->where( [
			'ID'          => $plugin_pages[ $page_id ],
			'post_status' => 'publish'
		] )
		->get_var();
}

function usp_delete_plugin_page( $page_id ) {
	$post_id = usp_get_plugin_page( $page_id );

	if ( ! $post_id ) {
		return false;
	}

	wp_delete_post( $post_id );

	$plugin_pages = get_site_option( 'usp_plugin_pages' );

	unset( $plugin_pages[ $page_id ] );

	update_site_option( 'usp_plugin_pages', $plugin_pages );
}

function usp_delete_plugin_pages() {
	$plugin_pages = get_site_option( 'usp_plugin_pages' );

	if ( ! $plugin_pages ) {
		return false;
	}

	foreach ( $plugin_pages as $page_id => $plugin_page ) {
		usp_delete_plugin_page( $page_id );
	}
}
