<?php

//добавляем стили колорпикера и другие в хеадер
add_action( 'wp_head', 'usp_inline_styles', 100 );
function usp_inline_styles() {

	list($r, $g, $b) = ($color = usp_get_option( 'primary-color' )) ? sscanf( $color, "#%02x%02x%02x" ) : array( 76, 140, 189 );

	$styles = apply_filters( 'usp_inline_styles', '', array( $r, $g, $b ) );

	if ( ! $styles )
		return false;

	// удаляем пробелы, переносы, табуляцию
	$styles = preg_replace( '/ {2,}/', '', str_replace( array( "\r\n", "\r", "\n", "\t" ), '', $styles ) );

	echo "<style>" . $styles . "</style>\r\n";
}

add_filter( 'usp_inline_styles', 'usp_default_inline_styles', 5, 2 );
function usp_default_inline_styles( $styles, $rgb ) {

	list($r, $g, $b) = $rgb;

	$styles .= 'a.recall-button,
    span.recall-button,
    .recall-button.usp-upload-button,
    input[type="submit"].recall-button,
    input[type="submit"] .recall-button,
    input[type="button"].recall-button,
    input[type="button"] .recall-button,
    a.recall-button:hover,
    .recall-button.usp-upload-button:hover,
    input[type="submit"].recall-button:hover,
    input[type="submit"] .recall-button:hover,
    input[type="button"].recall-button:hover,
    input[type="button"] .recall-button:hover{
        background: rgb(' . $r . ', ' . $g . ', ' . $b . ');
    }
    a.recall-button.active,
    a.recall-button.active:hover,
    a.recall-button.filter-active,
    a.recall-button.filter-active:hover,
    a.data-filter.filter-active,
    a.data-filter.filter-active:hover{
        background: rgba(' . $r . ', ' . $g . ', ' . $b . ', 0.4);
    }
    .usp_preloader i{
        color:rgb(' . $r . ',' . $g . ',' . $b . ');
    }
    .usp-user-getails .status-user-usp::before{
        border-left-color:rgb(' . $r . ',' . $g . ',' . $b . ');
    }
    .rows-list .status-user-usp::before{
        border-top-color:rgb(' . $r . ',' . $g . ',' . $b . ');
    }
    .status-user-usp{
        border-color:rgb(' . $r . ',' . $g . ',' . $b . ');
    }
    .usp-field-input input[type="checkbox"]:checked + label.block-label::before,
    .usp-field-input input[type="radio"]:checked + label.block-label::before{
        background:rgb(' . $r . ',' . $g . ',' . $b . ');
        border-color:rgb(' . $r . ',' . $g . ',' . $b . ');
    }';

	return $styles;
}

// background color button api
add_filter( 'usp_inline_styles', 'usp_api_button_inline_background', 10, 2 );
function usp_api_button_inline_background( $styles, $rgb ) {
	list($r, $g, $b) = $rgb;
	$background_color = $r . ',' . $g . ',' . $b;

	$styles .= '
		body .usp-bttn.usp-bttn__type-primary {
			background-color: rgb(' . $background_color . ');
		}
		.usp-bttn.usp-bttn__type-primary.usp-bttn__active {
			background-color: rgba(' . $r . ', ' . $g . ', ' . $b . ', 0.4);
		}
		.usp-bttn.usp-bttn__type-simple.usp-bttn__active {
			box-shadow: 0 -5px 0 -3px rgb(' . $r . ', ' . $g . ', ' . $b . ') inset;
		}
	';

	return $styles;
}

// color button api
add_filter( 'usp_inline_styles', 'usp_api_button_inline_color', 10 );
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
			font-size: ' . 0.86 * $size . 'px;
		}
		.usp-bttn.usp-bttn__size-standart {
			font-size: ' . $size . 'px;
		}
		.usp-bttn.usp-bttn__size-medium {
			font-size: ' . 1.16 * $size . 'px;
		}
		.usp-bttn__type-clear.usp-bttn__mod-only-icon.usp-bttn__size-medium,
		.usp-bttn.usp-bttn__size-large {
			font-size: ' . 1.33 * $size . 'px;
		}
		.usp-bttn.usp-bttn__size-big {
			font-size: ' . 1.5 * $size . 'px;
		}
		.usp-bttn__type-clear.usp-bttn__mod-only-icon.usp-bttn__size-large {
			font-size: ' . 1.66 * $size . 'px;
		}
		.usp-bttn__type-clear.usp-bttn__mod-only-icon.usp-bttn__size-big {
			font-size: ' . 2 * $size . 'px;
		}
	';

	return $styles;
}

// css variable
// Основные цвета WP-Recall переведем в css переменные
// для удобства: hex и rgb значения - чтобы потом самим css генерировать как прозрачность текста (rgba)
add_filter( 'usp_inline_styles', 'usp_css_variable', 10, 2 );
function usp_css_variable( $styles, $rgb ) {
	$usp_color = usp_get_option( 'primary-color', '#4c8cbd' );

	list($r, $g, $b) = $rgb;

	// темнее rgb
	$rd	 = round( $r * 0.45 );
	$gd	 = round( $g * 0.45 );
	$bd	 = round( $b * 0.45 );

	// ярче rgb
	$rl	 = round( $r * 1.4 );
	$gl	 = round( $g * 1.4 );
	$bl	 = round( $b * 1.4 );

	// инверт rgb
	$rf	 = round( 0.75 * (255 - $r) );
	$gf	 = round( 0.75 * (255 - $g) );
	$bf	 = round( 0.75 * (255 - $b) );

	// https://stackoverflow.com/questions/3942878/how-to-decide-font-color-in-white-or-black-depending-on-background-color
	$text_color	 = '#fff';
	$threshold	 = apply_filters( 'usp_text_color_threshold', 150 );
	if ( ($r * 0.299 + $g * 0.587 + $b * 0.114) > $threshold ) {
		$text_color = '#000';
	}

	$styles .= '
:root{
--uspText: ' . $text_color . ';
--uspHex:' . $usp_color . ';
--uspRgb:' . $r . ',' . $g . ',' . $b . ';
--uspRgbDark:' . $rd . ',' . $gd . ',' . $bd . ';
--uspRgbLight:' . $rl . ',' . $gl . ',' . $bl . ';
--uspRgbFlip:' . $rf . ',' . $gf . ',' . $bf . ';
}
';

	return $styles;
}

add_filter( 'the_content', 'usp_message_post_moderation' );
function usp_message_post_moderation( $content ) {
	global $post;

	if ( ! isset( $post ) || ! $post )
		return $content;

	if ( $post->post_status == 'pending' ) {
		$content = usp_get_notice( ['text' => __( 'Publication pending approval!', 'usp' ), 'type' => 'error' ] ) . $content;
	}

	if ( $post->post_status == 'draft' ) {
		$content = usp_get_notice( ['text' => __( 'Draft of a post!', 'usp' ), 'type' => 'error' ] ) . $content;
	}

	return $content;
}

add_action( 'wp', 'usp_post_bar_setup', 10 );
function usp_post_bar_setup() {
	do_action( 'usp_post_bar_setup' );
}

function usp_bar_add_icon( $id_icon, $args ) {
	global $usp_bar;
	if ( ! usp_get_option( 'view_recallbar' ) )
		return false;
	$usp_bar['icons'][$id_icon] = $args;
	return true;
}

function usp_bar_add_menu_item( $id_item, $args ) {
	global $usp_bar;
	if ( ! usp_get_option( 'view_recallbar' ) )
		return false;
	$usp_bar['menu'][$id_item] = $args;
	return true;
}

function usp_post_bar_add_item( $id_item, $args ) {
	global $usp_post_bar;

	if ( isset( $args['url'] ) )
		$args['href'] = $args['url'];

	$usp_post_bar['items'][$id_item] = $args;

	return true;
}

add_filter( 'the_content', 'usp_post_bar', 999 );
function usp_post_bar( $content ) {
	global $usp_post_bar;

	if ( doing_filter( 'get_the_excerpt' ) || ! is_single() || is_front_page() )
		return $content;

	$usp_bar_items = apply_filters( 'usp_post_bar_items', $usp_post_bar['items'] );

	if ( ! isset( $usp_bar_items ) || ! $usp_bar_items )
		return $content;


	$bar = '<div id="usp-post-bar">';

	foreach ( $usp_bar_items as $id_item => $item ) {

		$bar .= '<div id="bar-item-' . $id_item . '" class="post-bar-item">';

		$bar .= usp_get_button( $item );

		$bar .= '</div>';
	}

	$bar .= '</div>';

	$content = $bar . $content;


	return $content;
}

add_action( 'wp_footer', 'usp_init_footer_action', 100 );
function usp_init_footer_action() {
	echo '<script>usp_do_action("usp_footer")</script>';
}

add_action( 'wp_footer', 'usp_popup_contayner', 4 );
function usp_popup_contayner() {
	echo '<div id="usp-overlay"></div>
        <div id="usp-popup"></div>';
}
