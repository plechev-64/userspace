<?php

/**
 * Get UserSpace option
 *
 * @since 1.0
 *
 * @param string|array $option  String - Name of the option to retrieve.
 *                              array - if option is a group of settings ('group'). [0] group, [1] name option
 *                              for example: usp_get_option( [ 'uspc_opt', 'contact_panel' ], 1 )
 * @param string $default       Optional. Default value to return if the option does not exist.
 *
 * @return string               Option value if exist, or default in $default.
 */
function usp_get_option( $option, $default = false ) {
    if ( is_array( $option ) ) {
        if ( isset( $option[0] ) && isset( $option[1] ) ) {
            $option_group = $option[0];
            $option_name  = $option[1];

            return usp_search_in_group_option( $option_group, $option_name, $default );
        } else {
            return $default;
        }
    }

    return usp_search_in_option( $option, $default );
}

// search in simple options
function usp_search_in_option( $option_name, $default ) {
    $pre = apply_filters( "usp_pre_option_{$option_name}", false, $option_name, $default );

    if ( false !== $pre )
        return $pre;

    $usp_options = get_site_option( 'usp_global_options' );

    if ( isset( $usp_options[$option_name] ) ) {
        if ( $usp_options[$option_name] || is_numeric( $usp_options[$option_name] ) ) {
            return $usp_options[$option_name];
        }
    }

    return $default;
}

// search for an option in the settings group
function usp_search_in_group_option( $option_group, $option_name, $default ) {
    $pre = apply_filters( "usp_pre_option_{$option_group}_{$option_name}", false, $option_group, $option_name, $default );

    if ( false !== $pre )
        return $pre;

    $usp_options = get_site_option( 'usp_global_options' );

    if ( isset( $usp_options[$option_group][$option_name] ) ) {
        if ( $usp_options[$option_group][$option_name] || is_numeric( $usp_options[$option_group][$option_name] ) ) {
            return $usp_options[$option_group][$option_name];
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
