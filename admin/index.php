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
