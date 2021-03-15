<?php

function usp_isset_plugin_page( $page_id ) {
    return usp_get_plugin_page( $page_id ) ? true : false;
}

function usp_create_plugin_page( $page_id, $args ) {
    global $user_ID;

    $ID = wp_insert_post( wp_parse_args( $args, array(
        'post_status' => 'publish',
        'post_author' => $user_ID,
        'post_type'   => 'page'
        ) ) );

    if ( ! $ID )
        return false;

    $plugin_pages = get_site_option( 'usp_plugin_pages' );

    $plugin_pages[$page_id] = $ID;

    update_site_option( 'usp_plugin_pages', $plugin_pages );

    return $ID;
}

function usp_create_plugin_page_if_need( $page_id, $args ) {
    if ( ! usp_isset_plugin_page( $page_id ) ) {
        return usp_create_plugin_page( $page_id, $args );
    }
    return false;
}

function usp_get_plugin_page( $page_id ) {

    $plugin_pages = get_site_option( 'usp_plugin_pages' );

    if ( ! isset( $plugin_pages[$page_id] ) )
        return false;

    return RQ::tbl( new USP_Posts_Query() )
            ->select( 'ID' )
            ->where( [
                'ID'          => $plugin_pages[$page_id],
                'post_status' => 'publish'
            ] )
            ->get_var();
}

function usp_delete_plugin_page( $page_id ) {

    $ID = usp_get_plugin_page( $page_id );

    if ( ! $ID )
        return false;

    wp_delete_post( $ID );

    $plugin_pages = get_site_option( 'usp_plugin_pages' );

    unset( $plugin_pages[$page_id] );

    update_site_option( 'usp_plugin_pages', $plugin_pages );
}

function usp_delete_plugin_pages() {

    $plugin_pages = get_site_option( 'usp_plugin_pages' );

    if ( ! $plugin_pages )
        return false;

    foreach ( $plugin_pages as $page_id => $plugin_page ) {
        usp_delete_plugin_page( $page_id );
    }
}
