<?php

add_action( 'usp_enqueue_scripts', 'usp_default_theme_css', 10 );
function usp_default_theme_css() {
    if ( ! usp_is_office() )
        return;

    usp_enqueue_style( 'usp-theme-default-css', plugins_url( 'style.css', __FILE__ ) );
}

add_action( 'usp_enqueue_scripts', 'usp_default_theme_js' );
function usp_default_theme_js() {
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
add_action( 'widgets_init', 'usp_default_theme_sidebar' );
function usp_default_theme_sidebar() {
    register_sidebar( array(
        'name'          => __( 'UserSpace: Sidebar personal account content', 'userspace' ),
        'id'            => 'usp_theme_sidebar',
        'description'   => __( 'It is displayed only in user profile page. To the right of the content (sidebar)', 'userspace' ),
        'before_title'  => '<h3 class="theme_sidebar_title">',
        'after_title'   => '</h3>',
        'before_widget' => '<div class="theme_sidebar">',
        'after_widget'  => '</div>'
    ) );
}

add_action( 'widgets_init', 'usp_default_theme_sidebar_before' );
function usp_default_theme_sidebar_before() {
    register_sidebar( array(
        'name'          => __( 'UserSpace: Sidebar above personal account', 'userspace' ),
        'id'            => 'usp_theme_sidebar_before',
        'description'   => __( 'It is displayed only in user profile page.', 'userspace' ),
        'before_title'  => '<h3 class="theme_title_before">',
        'after_title'   => '</h3>',
        'before_widget' => '<div class="theme_sidebar theme_sidebar_before">',
        'after_widget'  => '</div>'
    ) );
}

add_action( 'widgets_init', 'usp_default_theme_sidebar_after' );
function usp_default_theme_sidebar_after() {
    register_sidebar( array(
        'name'          => __( 'UserSpace: Sidebar under personal account', 'userspace' ),
        'id'            => 'usp_theme_sidebar_after',
        'description'   => __( 'It is displayed only in user profile page.', 'userspace' ),
        'before_title'  => '<h3 class="theme_title_after">',
        'after_title'   => '</h3>',
        'before_widget' => '<div class="theme_sidebar theme_sidebar_after">',
        'after_widget'  => '</div>'
    ) );
}

add_action( 'usp_area_before', 'usp_add_sidebar_theme_area_before' );
function usp_add_sidebar_theme_area_before() {
    if ( function_exists( 'dynamic_sidebar' ) ) {
        dynamic_sidebar( 'usp_theme_sidebar_before' );
    }
}

add_action( 'usp_area_after', 'usp_add_sidebar_theme_area_after' );
function usp_add_sidebar_theme_area_after() {
    if ( function_exists( 'dynamic_sidebar' ) ) {
        dynamic_sidebar( 'usp_theme_sidebar_after' );
    }
}

// inline css
add_filter( 'usp_inline_styles', 'usp_add_theme_cover_inline_styles', 10 );
function usp_add_theme_cover_inline_styles( $styles ) {
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

    $styles .= '#usp-office-profile{background-image: url(' . $cover_url . '?vers=' . filemtime( $cover_path ) . ');}';

    return $styles;
}
