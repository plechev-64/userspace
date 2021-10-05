<?php

add_action( 'usp_enqueue_scripts', 'usp_userspace_bar_scripts', 10 );
function usp_userspace_bar_scripts() {
	usp_enqueue_style( 'usp-bar', USP_URL . 'modules/usp-bar/assets/css/usp-bar.css', false, false, true );
}

add_action( 'wp_footer', 'usp_userspace_bar_menu', 3 );
function usp_userspace_bar_menu() {
	usp_include_template( 'usp-bar.php' );
}

// remove offset in WordPress toolbar
add_action( 'get_header', 'usp_bar_remove_admin_bar' );
function usp_bar_remove_admin_bar() {
	if ( ! is_admin_bar_showing() ) {
		return;
	}

	remove_action( 'wp_head', '_admin_bar_bump_cb' );
}

// registering our offsets
add_filter( 'usp_inline_styles', 'usp_bar_offset_inline_styles', 10 );
function usp_bar_offset_inline_styles( $styles ) {
	if ( is_customize_preview() && usp_get_option_customizer( 'usp_bar_show', 1 ) == 0 ) {
		return $styles;
	}

	if ( is_admin_bar_showing() ) {
		// 32px WordPress toolbar + 40px (min) UserSpace bar = 72px
		// on 782px: 46 + 40 = 86
		$styles .= 'html, * html body {margin-top:72px !important;}
        #usp-bar{margin-top:32px;}
        @media screen and (max-width:782px) {
	        html, * html body {margin-top: 86px !important;}
	        #usp-bar{margin-top:46px;}
        }';
	} else {
		$styles .= 'html, * html body {margin-top:40px !important;}';
	}

	return $styles;
}

// added class in body tag
add_filter( 'body_class', 'usp_add_userbar_class_body' );
function usp_add_userbar_class_body( $classes ) {
	$classes[] = 'usp-userbar';
	$classes[] = 'usp-userbar-' . usp_get_option_customizer( 'usp_bar_color', 'dark' );

	return $classes;
}

// hide in the customizer if disabled bar
function usp_bar_customizer_hide() {
	if ( usp_get_option_customizer( 'usp_bar_show', 1 ) ) {
		return;
	}

	return 'style="display:none;"';
}

// get max width bar
function usp_bar_width() {
	$width = usp_get_option_customizer( 'usp_bar_width' );

	return $width ? 'style="max-width:' . $width . 'px;"' : 'style="max-width:calc(100% - 24px)"';
}
