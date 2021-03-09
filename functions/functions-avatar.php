<?php

add_action( 'wp_head', 'usp_register_avatar_sizes', 10 );
function usp_register_avatar_sizes() {

    $sizes = apply_filters( 'usp_avatar_sizes', [ 70, 150, 300 ] );

    asort( $sizes );

    foreach ( $sizes as $k => $size ) {
        add_image_size( 'usp-avatar-' . $size, $size, $size, 1 );
    }
}

add_action( 'usp_avatar', 'usp_setup_avatar_icons', 10 );
function usp_setup_avatar_icons() {

    $icons = usp_avatar_icons();

    if ( ! $icons )
        return false;

    $html = array();
    foreach ( $icons as $icon_id => $icon ) {

        $atts = array();

        if ( isset( $icon['atts'] ) ) {
            foreach ( $icon['atts'] as $attr => $val ) {
                $val    = (is_array( $val )) ? implode( ' ', $val ) : $val;
                $atts[] = $attr . '="' . $val . '"';
            }
        }

        $string = '<a class="usp-ava-bttn usps usps__line-1 icon-' . $icon_id . '" ' . implode( ' ', $atts ) . '>';

        if ( isset( $icon['icon'] ) )
            $string .= '<i class="uspi ' . $icon['icon'] . '"></i>';

        if ( isset( $icon['content'] ) )
            $string .= $icon['content'];

        $string .= '</a>';

        $html[] = $string;
    }

    echo '<div class="usp-ava-bttns">' . implode( '', $html ) . '</div>';
}

function usp_avatar_icons() {
    return apply_filters( 'usp_avatar_icons', array() );
}

// specifying the url before the uploaded avatar image
add_filter( 'pre_get_avatar_data', 'usp_avatar_data_replacement', 20, 2 );
function usp_avatar_data_replacement( $args, $id_or_email ) {
    global $usp_user;

    $size = $args['size'];

    $user_id     = 0;
    $avatar_data = false;

    if ( $usp_user && $usp_user->ID == $id_or_email ) {

        $user_id = $usp_user->ID;

        if ( isset( $usp_user->avatar_data ) && $usp_user->avatar_data ) {
            $avatar_data = $usp_user->avatar_data;
        }
    } else {

        if ( is_numeric( $id_or_email ) ) {
            $user_id = $id_or_email;
        } elseif ( is_object( $id_or_email ) ) {
            $user_id = $id_or_email->user_id;
        } elseif ( is_email( $id_or_email ) ) {
            if ( $user    = get_user_by( 'email', $id_or_email ) )
                $user_id = $user->ID;
        }
    }

    if ( $user_id ) {

        if ( ! $avatar_data )
            $avatar_data = get_user_meta( $user_id, 'usp_avatar', 1 );

        if ( ! $avatar_data ) {
            $avatar_data = usp_get_option( 'default_avatar', false );
        }

        if ( $avatar_data ) {

            $url = false;

            if ( is_numeric( $avatar_data ) ) {
                $image_attributes = wp_get_attachment_image_src( $avatar_data, array( $size, $size ) );
                if ( $image_attributes )
                    $url              = $image_attributes[0];
            }

            if ( $url && file_exists( usp_path_by_url( $url ) ) ) {
                $args['url'] = $url;
            }
        }
    }

    return $args;
}
