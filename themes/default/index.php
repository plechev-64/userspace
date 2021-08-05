<?php

add_action( 'usp_enqueue_scripts', 'usp_default_theme_css', 10 );
function usp_default_theme_css() {
	if ( ! usp_is_office() ) {
		return;
	}

	usp_enqueue_style( 'usp-theme-default-css', plugins_url( 'assets/css/usp-default-theme.css', __FILE__ ) );
}

add_action( 'usp_enqueue_scripts', 'usp_default_theme_js' );
function usp_default_theme_js() {
	if ( ! usp_is_office() ) {
		return;
	}

	if ( usp_get_option( 'usp_overflow_menu', 1 ) == 0 ) {
		return;
	}

	usp_enqueue_script( 'usp-theme-default-js', plugins_url( 'assets/js/usp-default-theme.js', __FILE__ ), false, true );
}

// support for the dashboard theme features
add_action( 'usp_init', 'usp_setup_template_options', 10 );
function usp_setup_template_options() {
	usp_template_support( 'avatar-uploader' );
	usp_template_support( 'cover-uploader' );
	usp_template_support( 'zoom-avatar' );
	//usp_template_support( 'modal-user-details' );
}

// registering 3 widget areas
add_action( 'widgets_init', 'usp_default_theme_sidebar' );
function usp_default_theme_sidebar() {
	register_sidebar( array(
		'name'          => __( 'UserSpace: Sidebar personal account content', 'userspace' ),
		'id'            => 'usp_theme_sidebar',
		'description'   => __( 'It is displayed only in user profile page. To the right of the content (sidebar)', 'userspace' ),
		'before_title'  => '<h3 class="usp-theme__sidebar-title">',
		'after_title'   => '</h3>',
		'before_widget' => '<div class="usp-right-sidebar usps usps__column usps__shrink-0">',
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
		'before_widget' => '<div class="usp-theme__sidebar usp-theme__sidebar-before">',
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
		'before_widget' => '<div class="usp-theme__sidebar usp-theme__sidebar-after">',
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
	if ( ! usp_is_office() ) {
		return $styles;
	}

	$cover = get_user_meta( USP()->office()->get_owner_id(), 'usp_cover', 1 );

	if ( ! $cover ) {
		$cover = usp_get_option( 'usp_default_cover', 0 );
	}

	$cover_url = wp_get_attachment_image_url( $cover, 'large' );

	if ( ! $cover_url ) {
		$cover_url = plugins_url( 'assets/img/usp-default-cover.jpg', __FILE__ );
	}

	$dataUrl    = wp_parse_url( $cover_url );
	$cover_path = untrailingslashit( ABSPATH ) . $dataUrl['path'];

	$styles .= '#usp-office-profile{background-image: url(' . $cover_url . '?vers=' . filemtime( $cover_path ) . ');}';

	return $styles;
}

add_filter( 'usp_options', 'usp_default_theme_settings', 12 );
function usp_default_theme_settings( $options ) {


	if ( $options->isset_box( 'primary' ) ) {

		$options->box( 'primary' )->group( 'design' )->add_options( array(
			[
				'type'    => 'radio',
				'slug'    => 'usp_office_tab_type',
				'title'   => __( 'The location of the section buttons', 'userspace' ),
				'values'  => [ __( 'Left', 'userspace' ), __( 'Top', 'userspace' ) ],
				'default' => 1,
			],
			[
				'title'   => __( 'When overflowing, hide the buttons in the drop-down menu?', 'userspace' ),
				'type'    => 'switch',
				'slug'    => 'usp_overflow_menu',
				'text'    => [
					'off' => __( 'No', 'userspace' ),
					'on'  => __( 'Yes', 'userspace' )
				],
				'default' => 1,
			],
		) );
	}

	return $options;
}

add_filter( 'usp_office_class', 'usp_default_theme_add_buttons_class' );
function usp_default_theme_add_buttons_class( $classes ) {
	$classes .= ( usp_get_option( 'usp_office_tab_type', 1 ) != 1 ) ? "usp-nav__column" : "usp-nav__row";

	return $classes;
}

add_action( 'usp_area_top', 'usp_add_office_logout', 10 );
function usp_add_office_logout() {
	if ( usp_get_option( 'usp_bar_show' ) || ! usp_is_office( get_current_user_id() ) ) {
		return;
	}

	echo usp_logout_shortcode( [ 'type' => 'clear' ] );
}
