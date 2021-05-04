<?php

function usp_support_zoom_avatar_scripts() {
    usp_enqueue_script( 'usp-user-info', USP_URL . 'functions/supports/js/zoom-avatar.js' );
}

add_filter( 'usp_avatar_icons', 'usp_zoom_avatar_button', 10 );
function usp_zoom_avatar_button( $icons ) {
    global $user_LK;

    $avatar = get_user_meta( $user_LK, 'usp_avatar', 1 );

    if ( $avatar ) {
        usp_dialog_scripts();
        usp_support_zoom_avatar_scripts();

        $icons['user-info'] = array(
            'icon' => 'fa-search',
            'atts' => array(
                'title'     => __( 'Zoom avatar', 'userspace' ),
                'onclick'   => 'usp_zoom_user_avatar(this);return false;',
                'url'       => '#',
                'data-zoom' => get_avatar_url( $user_LK, [ 'size' => 1000 ] )
            )
        );
    }

    return $icons;
}
