<?php

// adding colorpicker styles and others to the header
add_action( 'wp_head', 'usp_inline_styles', 100 );
function usp_inline_styles() {

    list($r, $g, $b) = ($color = usp_get_option( 'primary-color' )) ? sscanf( $color, "#%02x%02x%02x" ) : array( 76, 140, 189 );

    $inline_styles = apply_filters( 'usp_inline_styles', '', array( $r, $g, $b ) );

    if ( ! $inline_styles )
        return;

    // removing spaces, hyphenation, and tabs
    $src_cleared = preg_replace( '/ {2,}/', '', str_replace( array( "\r\n", "\r", "\n", "\t" ), '', $inline_styles ) );
    // space : and {
    $style       = str_replace( [ ': ', ' {' ], [ ':', '{' ], $src_cleared );

    echo "<style>" . $style . "</style>\r\n";
}

// color button api
//add_filter( 'usp_inline_styles', 'usp_api_button_inline_color', 10 );
function usp_api_button_inline_color( $styles ) {
    $color_button = usp_get_option( 'usp-button-text-color', '#fff' );

    $styles .= '
            body .usp-bttn.usp-bttn__type-primary {
                    color: ' . $color_button . ';
            }
	';

    return $styles;
}

// size button api
add_filter( 'usp_inline_styles', 'usp_api_button_inline_size', 10 );
function usp_api_button_inline_size( $styles ) {
    $size = usp_get_option( 'usp-button-font-size', '14' );

    $styles .= '
		body .usp-bttn,
		.usp-bttn.usp-bttn__size-small {
			font-size: ' . round( 0.86 * $size ) . 'px;
		}
		.usp-bttn.usp-bttn__size-standart {
			font-size: ' . $size . 'px;
		}
		.usp-bttn.usp-bttn__size-medium {
			font-size: ' . round( 1.14 * $size ) . 'px;
		}
		.usp-bttn__type-clear.usp-bttn__mod-only-icon.usp-bttn__size-medium,
		.usp-bttn.usp-bttn__size-large {
			font-size: ' . round( 1.28 * $size ) . 'px;
		}
		.usp-bttn.usp-bttn__size-big {
			font-size: ' . round( 1.5 * $size ) . 'px;
		}
		.usp-bttn__type-clear.usp-bttn__mod-only-icon.usp-bttn__size-large {
			font-size: ' . round( 1.8 * $size ) . 'px;
		}
		.usp-bttn__type-clear.usp-bttn__mod-only-icon.usp-bttn__size-big {
			font-size: ' . round( 2.1 * $size ) . 'px;
		}
	';

    return $styles;
}

// set frontend root inline css colors
add_filter( 'usp_inline_styles', 'usp_css_variable', 10, 2 );
function usp_css_variable( $styles, $rgb ) {
    $usp_color = usp_get_option( 'primary-color', '#4c8cbd' );

    list($r, $g, $b) = $rgb;

    $styles .= usp_get_root_colors( $r, $g, $b, $usp_color );

    return $styles;
}

function usp_bar_add_icon( $id_icon, $args ) {
    if ( ! usp_get_option( 'view_usp_bar' ) )
        return false;

    global $usp_bar;

    $usp_bar['icons'][$id_icon] = $args;

    return true;
}

function usp_bar_add_menu_item( $id_item, $args ) {
    if ( ! usp_get_option( 'view_usp_bar' ) )
        return false;

    global $usp_bar;

    $usp_bar['menu'][$id_item] = $args;

    return true;
}

//function usp_post_bar_add_item( $id_item, $args ) {
//    global $usp_post_bar;
//
//    if ( isset( $args['url'] ) )
//        $args['href'] = $args['url'];
//
//    $usp_post_bar['items'][$id_item] = $args;
//
//    return true;
//}
//
//add_filter( 'the_content', 'usp_post_bar', 999 );
//function usp_post_bar( $content ) {
//    global $usp_post_bar;
//
//    if ( doing_filter( 'get_the_excerpt' ) || ! is_single() || is_front_page() )
//        return $content;
//
//    $usp_bar_items = apply_filters( 'usp_post_bar_items', $usp_post_bar['items'] );
//
//    if ( ! isset( $usp_bar_items ) || ! $usp_bar_items )
//        return $content;
//
//
//    $bar = '<div id="usp-post-bar">';
//
//    foreach ( $usp_bar_items as $id_item => $item ) {
//
//        $bar .= '<div id="bar-item-' . $id_item . '" class="post-bar-item">';
//
//        $bar .= usp_get_button( $item );
//
//        $bar .= '</div>';
//    }
//
//    $bar .= '</div>';
//
//    $content = $bar . $content;
//
//
//    return $content;
//}

add_action( 'wp_footer', 'usp_init_footer_action', 100 );
function usp_init_footer_action() {
    echo '<script>usp_do_action("usp_footer")</script>';
}

add_action( 'wp_footer', 'usp_overlay_contayner', 4 );
function usp_overlay_contayner() {
    echo '<div id="usp-overlay"></div>';
}
