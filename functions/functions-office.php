<?php

add_action( 'usp_area_top', 'usp_add_office_menu_options', 10 );
function usp_add_office_menu_options() {
    echo USP()->tabs()->get_menu( 'options' );
}

add_action( 'usp_area_actions', 'usp_add_office_menu_actions', 10 );
function usp_add_office_menu_actions() {
    echo USP()->tabs()->get_menu( 'actions' );
}

add_action( 'usp_area_counters', 'usp_add_office_menu_counters', 10 );
function usp_add_office_menu_counters() {
    echo USP()->tabs()->get_menu( 'counters' );
}

add_action( 'usp_area_menu', 'usp_add_office_menu_menu', 10 );
function usp_add_office_menu_menu() {
    echo USP()->tabs()->get_menu( 'menu', [
        'class' => usp_get_option( 'buttons_place', 0 ) ? 'usps__column' : false
    ] );
}

add_action( 'usp_area_tabs', 'usp_add_office_tab_content', 10 );
function usp_add_office_tab_content() {
    if ( $current = USP()->tabs()->current() )
        echo $current->get_content();
    return false;
}

function usp_is_office( $user_id = null ) {
    global $usp_office;

    if ( isset( $_POST['action'] ) && $_POST['action'] == 'usp_ajax_tab' ) {

        $post = usp_decode_post( $_POST['post'] );

        if ( $post->master_id )
            $usp_office = $post->master_id;
    } else if ( USP_Ajax()->is_rest_request() ) {
        $usp_office = intval( $_POST['office_id'] );
    }

    if ( $usp_office ) {

        if ( isset( $user_id ) ) {
            if ( $user_id == $usp_office )
                return true;
            return false;
        }

        return true;
    }

    return false;
}

function usp_office_class() {
    global $active_addons, $user_LK, $user_ID;

    $class = array( 'usp-office' );

    $active_template = get_site_option( 'usp_active_template' );

    if ( $active_template ) {
        if ( isset( $active_addons[$active_template] ) )
            $class[] = 'office-' . strtolower( str_replace( ' ', '-', $active_addons[$active_template]['template'] ) );
    }

    if ( $user_ID ) {
        $class[] = ($user_LK == $user_ID) ? 'visitor-master' : 'visitor-guest';
    } else {
        $class[] = 'visitor-guest';
    }

    $class[] = (usp_get_option( 'buttons_place' ) == 1) ? "vertical-menu" : "horizontal-menu";

    echo 'class="' . implode( ' ', $class ) . '"';
}

function usp_template_support( $support ) {

    switch ( $support ) {
        case 'avatar-uploader':

            if ( usp_get_option( 'avatar_weight', 1024 ) > 0 )
                include_once USP_PATH . 'functions/supports/uploader-avatar.php';

            break;
        case 'cover-uploader':

            add_filter( 'usp_options', 'usp_add_cover_options', 10 );

            if ( usp_get_option( 'cover_weight', 1024 ) > 0 )
                include_once USP_PATH . 'functions/supports/uploader-cover.php';

            break;
        case 'modal-user-details':
            include_once USP_PATH . 'functions/supports/modal-user-details.php';
            break;
    }
}

function usp_add_balloon_menu( $data, $args ) {
    if ( $data['id'] != $args['tab_id'] )
        return $data;
    $data['name'] = sprintf( '%s <span class="usp-menu-notice usps__line-1">%s</span>', $data['name'], $args['ballon_value'] );
    return $data;
}
