<?php

if ( ! is_admin() ):
    add_action( 'usp_enqueue_scripts', 'usp_cab_15_scripts', 10 );
endif;
function usp_cab_15_scripts() {
    usp_enqueue_style( 'usp-theme-default', plugins_url( 'style.css', __FILE__ ) );
}

// инициализируем наши скрипты
add_action( 'usp_enqueue_scripts', 'cab_15_script_load' );
function cab_15_script_load() {
    global $user_LK;
    if ( $user_LK ) {
        usp_enqueue_script( 'theme-scripts', plugins_url( 'js/scripts.js', __FILE__ ), false, true );
    }
}

add_action( 'usp_init', 'usp_setup_template_options', 10 );
function usp_setup_template_options() {
    usp_template_support( 'avatar-uploader' );
    usp_template_support( 'cover-uploader' );
    usp_template_support( 'modal-user-details' );
}

// регистрируем 3 области виджетов
function cab_15_sidebar() {
    register_sidebar( array(
        'name'          => "USP: Сайдбар контента личного кабинета",
        'id'            => 'cab_15_sidebar',
        'description'   => 'Выводится только в личном кабинете. Справа от контента (сайдбар)',
        'before_title'  => '<h3 class="cabinet_sidebar_title">',
        'after_title'   => '</h3>',
        'before_widget' => '<div class="cabinet_sidebar">',
        'after_widget'  => '</div>'
    ) );
}

add_action( 'widgets_init', 'cab_15_sidebar' );
function cab_15_sidebar_before() {
    register_sidebar( array(
        'name'          => "USP: Сайдбар над личным кабинетом",
        'id'            => 'cab_15_sidebar_before',
        'description'   => 'Выводится только в личном кабинете',
        'before_title'  => '<h3 class="cab_title_before">',
        'after_title'   => '</h3>',
        'before_widget' => '<div class="cabinet_sidebar_before">',
        'after_widget'  => '</div>'
    ) );
}

add_action( 'widgets_init', 'cab_15_sidebar_before' );
function cab_15_sidebar_after() {
    register_sidebar( array(
        'name'          => "USP: Сайдбар под личным кабинетом",
        'id'            => 'cab_15_sidebar_after',
        'description'   => 'Выводится только в личном кабинете',
        'before_title'  => '<h3 class="cab_title_after">',
        'after_title'   => '</h3>',
        'before_widget' => '<div class="cabinet_sidebar_after">',
        'after_widget'  => '</div>'
    ) );
}

add_action( 'widgets_init', 'cab_15_sidebar_after' );

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

// корректирующие стили
add_filter( 'usp_inline_styles', 'usp_add_cover_inline_styles', 10 );
function usp_add_cover_inline_styles( $styles ) {

    if ( ! usp_is_office() )
        return $styles;

    global $user_LK;

    $cover = get_user_meta( $user_LK, 'usp_cover', 1 );

    if ( ! $cover )
        $cover = usp_get_option( 'default_cover', 0 );

    $cover_url = $cover && is_numeric( $cover ) ? wp_get_attachment_image_url( $cover, 'large' ) : $cover;

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

    $lca_hex = usp_get_option( 'primary-color' ); // достаем оттуда наш цвет
    list($r, $g, $b) = sscanf( $lca_hex, "#%02x%02x%02x" );

    $rp = round( $r * 0.90 );
    $gp = round( $g * 0.90 );
    $bp = round( $b * 0.90 );

    $styles .= '
    #lk-menu a:hover {
        background: rgba(' . $rp . ', ' . $gp . ', ' . $bp . ', 1);
    }
    #lk-menu a.active:hover {
        background: rgba(' . $r . ', ' . $g . ', ' . $b . ', 0.4);
    }';

    return $styles;
}
