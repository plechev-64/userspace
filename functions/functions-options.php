<?php

/**
 * Get UserSpace option.
 *
 * @param   $option     string  String - Name of the option to retrieve.
 *                              Array - if option is a group of settings ('group'). [0] group, [1] name option.
 *                              for example: usp_get_option( [ 'uspc_opt', 'contact_panel' ], 1 )
 * @param   $default    string  Optional. Default value to return if the option does not exist.
 *
 * @return  string              Option value, if it exists, or the default in $default.
 *
 * @see     USP_Options
 *
 * @since   1.0.0
 */
function usp_get_option( $option, $default = false ) {
	return USP()->options()->get( $option, $default );
}

/**
 * Update UserSpace option.
 *
 * @param   $name   string  Name of the option.
 * @param   $value  mixed   New value option.
 *
 * @see     USP_Options
 *
 * @since   1.0.0
 */
function usp_update_option( $name, $value ) {
	return USP()->options()->update( $name, $value );
}

/**
 * Delete UserSpace option.
 *
 * @param   $name   string  Name of the option.
 *
 * @see     USP_Options
 *
 * @since   1.0.0
 */
function usp_delete_option( $name ) {
	return USP()->options()->delete( $name );
}

/**
 * Get customizer option.
 *
 * @param   $option     string  Name of the option to retrieve.
 * @param   $default    string  Optional. Default value to return if the option does not exist.
 *
 * @return  string      Option value, if it exists, or default in $default.
 *
 * @since   1.0.0
 */
function usp_get_option_customizer( string $option, $default = false ) {
	$all_options = get_option( 'usp_customizer' );

	if ( $all_options && array_key_exists( $option, $all_options ) ) {
		return $all_options[ $option ];
	} else {
		return $default;
	}
}
