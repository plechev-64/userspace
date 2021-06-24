<?php

function usp_support_zoom_avatar_scripts() {
    usp_enqueue_script( 'usp-zoom-avatar', USP_URL . 'functions/supports/js/zoom-avatar.js' );
}

add_filter( 'usp_avatar_bttns', 'usp_zoom_avatar_button', 10 );
function usp_zoom_avatar_button( $buttons ) {
    $avatar = get_user_meta( usp_office_id(), 'usp_avatar', 1 );

    if ( $avatar ) {
        usp_dialog_scripts();
        usp_support_zoom_avatar_scripts();

        $args    = [
            'type'    => 'simple',
            'size'    => 'medium',
            'class'   => 'icon-zoom-avatar usp-ava-bttn usps__jc-center',
            'title'   => __( 'Zoom avatar', 'userspace' ),
            'onclick' => 'usp_zoom_user_avatar(this);return false;',
            'href'    => '#',
            'data'    => array(
                'zoom' => get_avatar_url( usp_office_id(), [ 'size' => 1000 ] )
            ),
            'icon'    => 'fa-search',
        ];
        $buttons .= usp_get_button( $args );
    }

    return $buttons;
}
