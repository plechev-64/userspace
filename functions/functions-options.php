<?php

/**
 * Get UserSpace option
 *
 * @param   string|array  $option   String - Name of the option to retrieve.
 *                                  array - if option is a group of settings ('group'). [0] group, [1] name option
 *                                  for example: usp_get_option( [ 'uspc_opt', 'contact_panel' ], 1 )
 * @param   string        $default  Optional. Default value to return if the option does not exist.
 *
 * @return string               Option value if exist, or default in $default.
 * @since 1.0
 *
 */
function usp_get_option( $option, $default = false ) {
	return USP()->options()->get( $option, $default );
}

function usp_update_option( $name, $value ) {
	return USP()->options()->update( $name, $value );
}

function usp_delete_option( $name ) {
	return USP()->options()->delete( $name );
}
