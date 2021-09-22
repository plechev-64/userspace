<?php

/* load user account page */
function userspace() {

	do_action( 'usp_area_before' );
	?>

    <div id="usp-office" class="<?php echo usp_get_office_class(); ?>"
         data-account="<?php echo USP()->office()->get_owner_id(); ?>">

		<?php do_action( 'usp_area_notice' ); ?>

		<?php
		if ( $themePath = USP()->theme()->get( 'path' ) ) {
			USP()->template( 'usp-office.php', $themePath )->include();
		} else {
			echo '<h3>' . __( 'Office templates not found!', 'userspace' ) . '</h3>';
		}
		?>

    </div>

	<?php
	do_action( 'usp_area_after' );
}

// adding colorpicker styles and others to the header
add_action( 'wp_head', 'usp_inline_styles', 100 );
function usp_inline_styles() {

	[ $r, $g, $b ] = sscanf( usp_get_option_customizer( 'usp_primary_color', '#0369a1' ), "#%02x%02x%02x" );

	$inline_styles = apply_filters( 'usp_inline_styles', '', [ $r, $g, $b ] );

	if ( ! $inline_styles ) {
		return;
	}

	// removing spaces, hyphenation, and tabs
	$src_cleared = preg_replace( '/ {2,}/', '', str_replace( [ "\r\n", "\r", "\n", "\t" ], '', $inline_styles ) );
	// space : and {
	$style = str_replace( [ ': ', ' {' ], [ ':', '{' ], $src_cleared );

	echo "<style>" . $style . "</style>\r\n";
}

// size button api
add_filter( 'usp_inline_styles', 'usp_api_button_inline_size', 10 );
function usp_api_button_inline_size( $styles ) {
	$size = usp_get_option_customizer( 'usp_bttn_size', '15' );

	$styles .= '
		.usp-bttn__size-small > * {
			font-size: ' . round( 0.86 * $size ) . 'px;
		}
		.usp-bttn__size-standard > * {
			font-size: ' . $size . 'px;
		}
		.usp-bttn__size-medium > * {
			font-size: ' . round( 1.14 * $size ) . 'px;
		}
		.usp-bttn__type-clear.usp-bttn__mod-only-icon.usp-bttn__size-medium > *,
		.usp-bttn__size-large:not(.usp-bttn__mod-only-icon) > * {
			font-size: ' . round( 1.28 * $size ) . 'px;
		}
		.usp-bttn__size-big > * {
			font-size: ' . round( 1.5 * $size ) . 'px;
		}
		.usp-bttn__type-clear.usp-bttn__mod-only-icon.usp-bttn__size-large > * {
			font-size: ' . round( 1.8 * $size ) . 'px;
		}
		.usp-bttn__type-clear.usp-bttn__mod-only-icon.usp-bttn__size-big > * {
			font-size: ' . round( 2.1 * $size ) . 'px;
		}
	';

	return $styles;
}

// set frontend root inline css colors
add_filter( 'usp_inline_styles', 'usp_css_variable', 10 );
function usp_css_variable( $styles ) {
	$styles .= usp_get_root_colors();

	return $styles;
}

add_filter( 'the_content', 'usp_before_post', 999 );
function usp_before_post( $content ) {
	if ( doing_filter( 'get_the_excerpt' ) || ! is_single() || is_front_page() ) {
		return $content;
	}

	/**
	 * Adding buttons before the content.
	 *
	 * @param string    added buttons before the content.
	 *                  Default: empty string
	 *
	 * @since 1.0
	 *
	 */
	$before_post = apply_filters( 'usp_before_content_buttons', '' );

	$before = '<div id="usp-top-post-bttns" class="usp-top-post-bttns usp-post-bttns usps usps__jc-end">' . $before_post . '</div>';

	/**
	 * Adding buttons after the content.
	 *
	 * @param string    added buttons after the content.
	 *                  Default: empty string
	 *
	 * @since 1.0
	 *
	 */
	$after_post = apply_filters( 'usp_after_content_buttons', '' );

	$after = '<div id="usp-bottom-post-bttns" class="usp-bottom-post-bttns usp-post-bttns usps usps__jc-end">' . $after_post . '</div>';

	return $before . $content . $after;
}

add_action( 'wp_footer', 'usp_init_footer_action', 100 );
function usp_init_footer_action() {
	echo '<script>usp_do_action("usp_footer")</script>';
}

add_action( 'wp_footer', 'usp_overlay_contayner', 4 );
function usp_overlay_contayner() {
	echo '<div id="usp-overlay"></div>';
}

/**
 * Catch logout command
 * ?usp-logout=1
 *
 * @return redirect on home page.
 * @since 1.0
 *
 */
add_action( 'init', 'usp_wait_logout_get' );
function usp_wait_logout_get() {
	if ( ! is_user_logged_in() ) {
		return;
	}

	if ( isset( $_GET['usp-logout'] ) && ( $_GET['usp-logout'] == '1' ) ) {
		$url = apply_filters( 'usp_logout_url_redirect', get_home_url() );

		wp_logout();

		wp_safe_redirect( esc_url( $url ) );
		exit;
	}
}
