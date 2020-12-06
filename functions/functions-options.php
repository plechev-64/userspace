<?php

function usp_get_option( $option, $default = false ) {

	$pre = apply_filters( "usp_pre_option_{$option}", false, $option, $default );

	if ( false !== $pre )
		return $pre;

	$usp_options = get_site_option( 'usp_global_options' );

	if ( isset( $usp_options[$option] ) ) {
		if ( $usp_options[$option] || is_numeric( $usp_options[$option] ) ) {
			return $usp_options[$option];
		}
	}

	return $default;
}

function usp_update_option( $name, $value ) {

	$usp_options = get_site_option( 'usp_global_options' );

	$usp_options[$name] = $value;

	return update_site_option( 'usp_global_options', $usp_options );
}

function usp_delete_option( $name ) {

	$usp_options = get_site_option( 'usp_global_options' );

	unset( $usp_options[$name] );

	return update_site_option( 'usp_global_options', $usp_options );
}
