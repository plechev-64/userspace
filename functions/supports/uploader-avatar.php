<?php

USP()->use_module( 'uploader' );

if ( ! is_admin() ):
    add_action( 'usp_enqueue_scripts', 'usp_support_avatar_uploader_scripts', 10 );
endif;
function usp_support_avatar_uploader_scripts() {
    global $user_ID;
    if ( usp_is_office( $user_ID ) ) {
        usp_enqueue_script( 'avatar-uploader', USP_URL . 'functions/supports/js/uploader-avatar.js', false, true );
    }
}

add_filter( 'usp_init_js_variables', 'usp_init_js_avatar_variables', 10 );
function usp_init_js_avatar_variables( $data ) {
    global $user_ID;

    if ( usp_is_office( $user_ID ) ) {
        $data['avatar_size']                  = usp_get_option( 'avatar_weight', 1024 );
        $data['local']['upload_size_avatar']  = sprintf( __( 'Exceeds the maximum image size! Max. %s Kb', 'usp' ), usp_get_option( 'avatar_weight', 1024 ) );
        $data['local']['title_image_upload']  = __( 'Image being loaded', 'usp' );
        $data['local']['title_webcam_upload'] = __( 'Image from camera', 'usp' );
    }

    return $data;
}

add_filter( 'usp_avatar_icons', 'usp_button_avatar_upload', 10 );
function usp_button_avatar_upload( $icons ) {
    global $user_ID;

    if ( ! usp_is_office( $user_ID ) )
        return false;

    USP()->use_module( 'uploader' );

    $uploder = new USP_Uploader( 'usp_avatar', array(
        'multiple'    => 0,
        'crop'        => 1,
        'filetitle'   => 'usp-user-avatar-' . $user_ID,
        'filename'    => $user_ID,
        'dir'         => '/uploads/usp-uploads/avatars',
        'image_sizes' => array(
            array(
                'height' => 70,
                'width'  => 70,
                'crop'   => 1
            ),
            array(
                'height' => 150,
                'width'  => 150,
                'crop'   => 1
            ),
            array(
                'height' => 300,
                'width'  => 300,
                'crop'   => 1
            )
        ),
        'resize'      => array( 1000, 1000 ),
        'min_height'  => 150,
        'min_width'   => 150,
        'max_size'    => usp_get_option( 'avatar_weight', 1024 )
        ) );

    $icons['avatar-upload'] = array(
        'icon'    => 'fa-download',
        'content' => $uploder->get_input(),
        'atts'    => array(
            'title' => __( 'Avatar upload', 'usp' ),
            'url'   => '#'
        )
    );

    if ( get_user_meta( $user_ID, 'usp_avatar', 1 ) ) {

        $icons['avatar-delete'] = array(
            'icon' => 'fa-times',
            'atts' => array(
                'title' => __( 'Delete avatar', 'usp' ),
                'href'  => wp_nonce_url( add_query_arg( [ 'usp-action' => 'delete_avatar' ], usp_get_user_url( $user_ID ) ), $user_ID )
            )
        );
    }

    if ( isset( $_SERVER["HTTPS"] ) && $_SERVER["HTTPS"] == 'on' ) {

        /* usp_webcam_scripts();

          $icons['webcam-upload'] = array(
          'icon'	 => 'fa-camera',
          'atts'	 => array(
          'title'	 => __( 'Webcam screen', 'usp' ),
          'id'	 => 'webcamupload',
          'url'	 => '#'
          )
          ); */
    }

    return $icons;
}

add_action( 'usp_pre_upload', 'usp_avatar_pre_upload', 10 );
function usp_avatar_pre_upload( $uploader ) {
    global $user_ID;

    if ( $uploader->uploader_id != 'usp_avatar' )
        return;

    if ( $oldAvatarId = get_user_meta( $user_ID, 'usp_avatar', 1 ) )
        wp_delete_attachment( $oldAvatarId );
}

add_action( 'usp_upload', 'usp_avatar_upload', 10, 2 );
function usp_avatar_upload( $uploads, $uploader ) {
    global $user_ID;

    if ( $uploader->uploader_id != 'usp_avatar' )
        return;

    update_user_meta( $user_ID, 'usp_avatar', $uploads['id'] );

    do_action( 'usp_avatar_upload' );
}

add_action( 'wp', 'usp_delete_avatar_action' );
function usp_delete_avatar_action() {
    global $wpdb, $user_ID, $usp_avatar_sizes;
    if ( ! isset( $_GET['usp-action'] ) || $_GET['usp-action'] != 'delete_avatar' )
        return false;
    if ( ! wp_verify_nonce( $_GET['_wpnonce'], $user_ID ) )
        wp_die( 'Error' );

    $result = delete_user_meta( $user_ID, 'usp_avatar' );

    if ( ! $result )
        wp_die( 'Error' );

    $dir_path = USP_UPLOAD_PATH . 'avatars/';
    foreach ( $usp_avatar_sizes as $key => $size ) {
        unlink( $dir_path . $user_ID . '-' . $size . '.jpg' );
    }

    unlink( $dir_path . $user_ID . '.jpg' );

    do_action( 'usp_delete_avatar' );

    wp_redirect( add_query_arg( [ 'usp-avatar' => 'deleted' ], usp_get_user_url( $user_ID ) ) );
    exit;
}

add_action( 'wp', 'usp_notice_avatar_deleted' );
function usp_notice_avatar_deleted() {
    if ( isset( $_GET['usp-avatar'] ) && $_GET['usp-avatar'] == 'deleted' )
        add_action( 'usp_area_notice', function() {
            echo usp_get_notice( [ 'type' => 'success', 'text' => __( 'Your avatar has been deleted', 'usp' ) ] );
        } );
}

// disabling caching in chrome
add_filter( 'get_avatar_data', 'usp_add_avatar_time_creation', 10, 2 );
function usp_add_avatar_time_creation( $args, $id_or_email ) {
    $dataUrl     = wp_parse_url( $args['url'] );
    $ava_path    = untrailingslashit( ABSPATH ) . $dataUrl['path'];
    if ( ! file_exists( $ava_path ) )
        return $args;
    $args['url'] = $args['url'] . '?ver=' . filemtime( $ava_path );
    return $args;
}
