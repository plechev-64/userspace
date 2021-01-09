<?php

add_action( 'usp_enqueue_scripts', 'usp_default_cabinet_css', 10 );
function usp_default_cabinet_css() {
    if ( ! usp_is_office() )
        return;

    usp_enqueue_style( 'usp-theme-default-css', plugins_url( 'style.css', __FILE__ ) );
}

add_action( 'usp_enqueue_scripts', 'usp_default_cabinet_js' );
function usp_default_cabinet_js() {
    if ( ! usp_is_office() )
        return;

    usp_enqueue_script( 'usp-theme-default-js', plugins_url( 'js/scripts.js', __FILE__ ), false, true );
}

// support for the dashboard theme features
add_action( 'usp_init', 'usp_setup_template_options', 10 );
function usp_setup_template_options() {
    usp_template_support( 'avatar-uploader' );
    usp_template_support( 'cover-uploader' );
    usp_template_support( 'modal-user-details' );
}

// registering 3 widget areas
add_action( 'widgets_init', 'cab_15_sidebar' );
function cab_15_sidebar() {
    register_sidebar( array(
        'name'          => __( 'UserSpace: Sidebar personal account content', 'userspace' ),
        'id'            => 'cab_15_sidebar',
        'description'   => __( 'It is displayed only in personal account. To the right of the content (sidebar)', 'userspace' ),
        'before_title'  => '<h3 class="cabinet_sidebar_title">',
        'after_title'   => '</h3>',
        'before_widget' => '<div class="cabinet_sidebar">',
        'after_widget'  => '</div>'
    ) );
}

add_action( 'widgets_init', 'cab_15_sidebar_before' );
function cab_15_sidebar_before() {
    register_sidebar( array(
        'name'          => __( 'UserSpace: Sidebar above personal account', 'userspace' ),
        'id'            => 'cab_15_sidebar_before',
        'description'   => __( 'It is displayed only in personal account.', 'userspace' ),
        'before_title'  => '<h3 class="cab_title_before">',
        'after_title'   => '</h3>',
        'before_widget' => '<div class="cabinet_sidebar_before">',
        'after_widget'  => '</div>'
    ) );
}

add_action( 'widgets_init', 'cab_15_sidebar_after' );
function cab_15_sidebar_after() {
    register_sidebar( array(
        'name'          => __( 'UserSpace: Sidebar under personal account', 'userspace' ),
        'id'            => 'cab_15_sidebar_after',
        'description'   => __( 'It is displayed only in personal account.', 'userspace' ),
        'before_title'  => '<h3 class="cab_title_after">',
        'after_title'   => '</h3>',
        'before_widget' => '<div class="cabinet_sidebar_after">',
        'after_widget'  => '</div>'
    ) );
}

add_action( 'usp_area_before', 'usp_add_sidebar_area_before' );
function usp_add_sidebar_area_before() {
    if ( function_exists( 'dynamic_sidebar' ) ) {
        dynamic_sidebar( 'cab_15_sidebar_before' );
    }
}

add_action( 'usp_area_after', 'usp_add_sidebar_area_after' );
function usp_add_sidebar_area_after() {
    if ( function_exists( 'dynamic_sidebar' ) ) {
        dynamic_sidebar( 'cab_15_sidebar_after' );
    }
}

// inline css
add_filter( 'usp_inline_styles', 'usp_add_cover_inline_styles', 10 );
function usp_add_cover_inline_styles( $styles ) {
    if ( ! usp_is_office() )
        return $styles;

    global $user_LK;

    $cover = get_user_meta( $user_LK, 'usp_cover', 1 );

    if ( ! $cover )
        $cover = usp_get_option( 'default_cover', 0 );

    $cover_url = wp_get_attachment_image_url( $cover, 'large' );

    if ( ! $cover_url )
        $cover_url = plugins_url( 'img/default-cover.jpg', __FILE__ );

    $dataUrl    = wp_parse_url( $cover_url );
    $cover_path = untrailingslashit( ABSPATH ) . $dataUrl['path'];

    $styles .= '#lk-conteyner{background-image: url(' . $cover_url . '?vers=' . filemtime( $cover_path ) . ');}';

    return $styles;
}

add_filter( 'usp_inline_styles', 'usp_add_colors_inline_styles', 10 );
function usp_add_colors_inline_styles( $styles ) {
    if ( ! usp_is_office() )
        return $styles;

    $lca_hex = usp_get_option( 'primary-color' );
    list($r, $g, $b) = sscanf( $lca_hex, "#%02x%02x%02x" );

    $rp = round( $r * 0.90 );
    $gp = round( $g * 0.90 );
    $bp = round( $b * 0.90 );

    $styles .= '
    #lk-menu a:hover {
        background: rgba(' . $rp . ', ' . $gp . ', ' . $bp . ', 1);
    }
    #lk-menu a.active:hover {
        background: rgba(' . $r . ', ' . $g . ', ' . $b . ', .4);
    }';

    return $styles;
}
