<?php

if ( ! is_admin() ):
    add_action( 'usp_enqueue_scripts', 'usp_support_user_info_scripts', 10 );
endif;
function usp_support_user_info_scripts() {
    if ( usp_is_office() ) {
        usp_dialog_scripts();
        usp_enqueue_script( 'usp-user-info', USP_URL . 'functions/supports/js/user-details.js' );
    }
}

add_filter( 'usp_init_js_variables', 'usp_init_js_user_info_variables', 10 );
function usp_init_js_user_info_variables( $data ) {

    if ( usp_is_office() ) {
        $data['local']['title_user_info'] = __( 'Detailed information', 'userspace' );
    }

    return $data;
}

add_filter( 'usp_avatar_icons', 'usp_add_user_info_button', 10 );
function usp_add_user_info_button( $icons ) {

    usp_dialog_scripts();

    $icons['user-info'] = array(
        'icon' => 'fa-info-circle',
        'atts' => array(
            'title'   => __( 'User info', 'userspace' ),
            'onclick' => 'usp_get_user_info(this);return false;',
            'url'     => '#'
        )
    );

    return $icons;
}

usp_ajax_action( 'usp_return_user_details', true );
function usp_return_user_details() {

    return array(
        'content' => usp_get_user_details( intval( $_POST['user_id'] ) )
    );
}

function usp_get_user_details( $user_id, $set_args = false ) {
    global $user_LK, $usp_blocks;

    $user_LK = $user_id;

    $defaults = array(
        'zoom'          => true,
        'description'   => true,
        'custom_fields' => true
    );

    $args = wp_parse_args( $set_args, $defaults );

    // if ( ! class_exists( 'USP_Blocks' ) )
    //    require_once USP_PATH . 'deprecated/class-usp-blocks.php';

    $content = '<div id="usp-user-details">';
    $content .= '<div class="usp-user-avatar usps__relative">';
    $content .= get_avatar( $user_LK, 600, false, false, [ 'class' => 'usp-detailed-ava usps__img-reset' ] );

    if ( $args['zoom'] ) {
        $avatar = get_user_meta( $user_LK, 'usp_avatar', 1 );

        if ( $avatar ) {
            $url_avatar = get_avatar_url( $user_LK, [ 'size' => 1000 ] );
            $content    .= '<a title="' . __( 'Zoom avatar', 'userspace' ) . '" data-zoom="' . $url_avatar . '" onclick="usp_zoom_avatar(this);return false;" class="usp-avatar-zoom usps__hidden" href="#"><i class="uspi fa-search-plus usps usps__column usps__jc-center usps__grow"></i></a>';
        }
    }

    $content .= '</div>';

    if ( $args['description'] ) {
        $content .= usp_get_quote_box( $user_LK, [ 'side' => 'top' ] );
    }

    if ( $args['custom_fields'] ) {

        if ( $usp_blocks && (isset( $usp_blocks['details'] ) || isset( $usp_blocks['content'] )) ) {

            $details    = isset( $usp_blocks['details'] ) ? $usp_blocks['details'] : array();
            $old_output = isset( $usp_blocks['content'] ) ? $usp_blocks['content'] : array();

            $details = array_merge( $details, $old_output );

            foreach ( $details as $a => $detail ) {
                if ( ! isset( $details[$a]['args']['order'] ) )
                    $details[$a]['args']['order'] = 10;
            }

            for ( $a = 0; $a < count( $details ); $a ++ ) {

                $min      = $details[$a];
                $newArray = $details;

                for ( $n = $a; $n < count( $newArray ); $n ++ ) {

                    if ( $newArray[$n]['args']['order'] < $min['args']['order'] ) {
                        $details[$n] = $min;
                        $min         = $newArray[$n];
                        $details[$a] = $min;
                    }
                }
            }

            foreach ( $details as $block ) {
                $USP_Blocks = new USP_Blocks( $block );
                $content    .= $USP_Blocks->get_block( $user_LK );
            }
        }
    }

    $content .= '</div>';

    return $content;
}
