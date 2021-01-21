<?php

require_once "admin-menu.php";

add_action( 'admin_init', 'usp_admin_scripts', 10 );

add_filter( 'display_post_states', 'usp_mark_own_page', 10, 2 );
function usp_mark_own_page( $post_states, $post ) {

    if ( $post->post_type === 'page' ) {

        $plugin_pages = get_site_option( 'usp_plugin_pages' );

        if ( ! $plugin_pages )
            return $post_states;

        if ( in_array( $post->ID, $plugin_pages ) ) {
            $post_states[] = __( 'The page of plugin UserSpace', 'userspace' );
        }
    }

    return $post_states;
}

// set admin area root inline css colors
add_filter( 'admin_head', 'usp_admin_css_variable' );
function usp_admin_css_variable() {
    $usp_color = usp_get_option( 'primary-color' );

    list($r, $g, $b) = ($usp_color = usp_get_option( 'primary-color' )) ? sscanf( $usp_color, "#%02x%02x%02x" ) : array( 76, 140, 189 );

    echo '<style>' . usp_get_root_colors( $r, $g, $b, $usp_color ) . '</style>';
}
