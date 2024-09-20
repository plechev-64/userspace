<?php

// Enable shortcodes in widgets
add_filter( 'widget_text', 'do_shortcode' );

add_shortcode( 'userspace', 'usp_userspace_shortcode' );
/**
 * Builds the personal account shortcode output.
 * Example: [userspace]
 *
 * @return string|bool  HTML content to display personal account.
 * @since               1.0.0
 *
 */
function usp_userspace_shortcode(): bool|string {
//	if ( USP()->office()->on_page() && empty( USP()->office()->get_var( 'member' ) ) ) {
//		return usp_get_variations_buttons();
//	}

	/**
	 * Filters allow closes someone else's personal account.
	 *
	 * @param bool  Set 1 if you need to close someone else's personal account.
	 *              Default: false
	 *
	 * @since       1.0.0
	 *
	 */
	$true_private = apply_filters( 'usp_close_other_personal_account', false );

	if ( $true_private && ! usp_is_office( get_current_user_id() ) ) {

		/**
		 * Filters set the text about the closed personal account.
		 *
		 * @param string    Message or nothing.
		 *                  Default: false
		 *
		 * @since           1.0.0
		 *
		 */
		return apply_filters( 'usp_text_close_other_personal_account', false );
	}

	ob_start();

	userspace();

	$content = ob_get_contents();
	ob_end_clean();

	return $content;
}

/**
 * Displays the "Go to personal account" button or the buttons to Sign in and register.
 *
 * @return string   Buttons.
 * @since           1.0.0
 *
 */
function usp_get_variations_buttons() {
	if ( is_user_logged_in() ) {
		return usp_get_button( [
			'label' => __( 'Go to personal account', 'userspace' ),
			'icon'  => 'fa-user',
			'href'  => usp_user_get_url( get_current_user_id() )
		] );
	}

	return usp_control_panel_shortcode();
}

add_shortcode( 'usp-user-widget', 'usp_control_panel_shortcode' );
/**
 * Builds shortcode output the logged-in user control panel and login buttons to guest.
 *
 * If it is not logged in, it will display the login and registration buttons.
 * If logged in, it displays the avatar of the current user and the buttons to go to the personal account and exit the site.
 *
 * Example: [usp-user-widget]
 *
 * @return string   HTML content to display control panel.
 * @since           1.0.0
 *
 */
function usp_control_panel_shortcode() {
	return usp_get_include_template( 'usp-control-panel.php' );
}

add_shortcode( 'usp-loginform', 'usp_loginform_shortcode' );
/**
 * Builds shortcode output to display login, registration, and reset password forms.
 * If logged in - displays the button to go to the personal account.
 *
 * Example: [usp-loginform]
 *
 * @param   $args   array   Additional settings:
 * <pre>
 *  $args['active'] string  Set active tab in form.
 *                          Default: 'login'
 *                          Allowed: login|register|lostpassword
 *  $args['forms']  string  What forms to display in tabs.
 *                          Default: login,register,lostpassword
 *</pre>
 *
 * @return string       HTML content to display loginform.
 * @since               1.0.0
 *
 */
function usp_loginform_shortcode( $args = [] ) {
	if ( is_user_logged_in() ) {
		$url = '<a href="' . usp_user_get_url() . '">' . __( 'personal account', 'userspace' ) . '</a>';

		return usp_get_notice( [
			'type' => 'success',
			// translators: %s - url to "personal account"
			'text' => sprintf( __( 'You are already logged into the site. Go to your %s, to get started.', 'userspace' ), $url )
		] );
	}

	//use module loginform
	return usp_get_loginform( $args );
}

add_shortcode( 'usp-logout', 'usp_logout_shortcode' );
/**
 * Builds shortcode of the exit button from the site.
 * The button is shown only to logged-in users.
 *
 * Example: [usp-logout redirect="current"]
 *
 * @param   $args   array   Additional settings:
 * <pre>
 * $args['text']        string  Text on button.
 * $args['redirect']    string  Redirect after logout.
 *                              Default: 'home'
 *                              Allowed: home|current
 * $args['type']        string  Type of button.
 *                              Default: 'primary'
 *                              Allowed: primary|simple|clear
 * $args['icon']        string  Icon of button. Example: fa-sign-out
 *                              Default: false
 * </pre>
 *
 * @return string       HTML logout button.
 * @since               1.0.0
 *
 */
function usp_logout_shortcode( $args ) {
	if ( ! is_user_logged_in() ) {
		return false;
	}

	$atts = shortcode_atts( [
		'text'     => __( 'Log Out', 'userspace' ),
		'redirect' => 'home',
		'type'     => 'primary',
		'icon'     => false,
	], $args, 'usp-logout' );

	return usp_logout_button( $atts );
}

/**
 * Get logout button.
 *
 * @param $args         array   Additional settings:
 * <pre>
 * $args['text']        string  Text on button.
 * $args['redirect']    string  Redirect after logout.
 *                              Default: 'home'
 *                              Allowed: home|current
 * $args['type']        string  Type of button.
 *                              Default: 'primary'
 *                              Allowed: primary|simple|clear
 * $args['icon']        string  Icon of button. Example: fa-sign-out
 *                              Default: false
 * </pre>
 *
 * @return string
 * @since               1.0.0
 *
 */
function usp_logout_button( $args ) {
	$url = ( 'current' == $args['redirect'] ) ? get_the_permalink() : get_home_url();

	return usp_get_button( [
		'label' => $args['text'],
		'type'  => $args['type'],
		'icon'  => $args['icon'],
		'href'  => esc_url( wp_logout_url( $url ) )
	] );
}

add_shortcode( 'usp-cache', 'usp_cache_shortcode' );
/**
 * Builds shortcode allows you to cache the content.
 * As a rule, these are other shortcodes.
 *
 * Example: [usp-cache time="3600" key="my-unique-key"]say hello shortcode[/usp-cache]
 *
 * @param   $args       array   Additional settings:
 * <pre>
 * $args['time']        int     Caching time in seconds.
 * $args['key']         string  Unique key.
 * $args['only_guest']  int     Cached for guests only (set "1" - enables only guests).
 *                              Default: false
 * </pre>
 *
 * @return string       HTML cached content.
 * @since               1.0.0
 *
 */
function usp_cache_shortcode( $args, $content_in = null ) {
	global $post;

	$attr = shortcode_atts( [
		'key'        => '',
		'only_guest' => false,
		'time'       => false
	], $args );

	if ( 'publish' == $post->post_status ) {

		$attr['key'] .= '-cache-' . $post->ID;

		$usp_cache = new USP_Cache( $attr['time'], $attr['only_guest'] );

		if ( $usp_cache->is_cache ) {

			$file = $usp_cache->get_file( $attr['key'] );

			if ( ! $file->need_update ) {
				return $usp_cache->get_cache();
			}
		}
	}

	$content = do_shortcode( shortcode_unautop( $content_in ) );

	if ( '</p>' == substr( $content, 0, 4 ) && '<p>' == substr( $content, strlen( $content ) - 3 ) ) {
		$content = substr( $content, 4, strlen( $content ) - 7 );
	}

	if ( 'publish' == $post->post_status ) {

		if ( isset( $usp_cache ) && $usp_cache->is_cache ) {
			$usp_cache->update_cache( $content );
		}
	}

	return $content;
}


add_shortcode( 'usp-notice', 'usp_notice_shortcode' );
/**
 * Builds shortcode, displays a notification window.
 *
 * Example: [usp-notice text="Your message for guest" only_guests="1"]
 *
 * @param   $args       array   Additional settings:
 * <pre>
 * $args['text']        string      Required. Text message.
 * $args['title']       string      Title text.
 * $args['type']        string      Type notice.
 *                                  Allowed: info|success|warning|error|simple
 *                                  Default: info.
 * $args['text_center'] int         Text align.
 *                                  Allowed: 1 - text-align:center; 0 - left text position.
 *                                  Default: 1
 * $args['icon']        int|string  left position icon.
 *                                  Allowed: 0 - don't show, string - icon class. Example: 'fa-info'.
 *                                  Default: 1
 * $args['cookie']      string      Unique cookie id. Lowercase alphanumeric characters, dashes, and underscores are allowed.
 *                                  If a unique cookie id is specified, it becomes possible to hide the notification for the number of days specified in $args['cookie_time']
 *                                  Default: false
 * $args['cookie_time'] int         lifetime cookie in days.
 *                                  Default: 30
 * $args['only_guests'] int         If you need to show the notification only to guests (set: 1).
 *                                  Default: 0
 * </pre>
 *
 * @return string           HTML notice box.
 * @since                   1.0.0
 *
 */
function usp_notice_shortcode( $args ) {
	$atts = shortcode_atts( [
		'type'        => 'info',
		'title'       => false,
		'text'        => '',
		'text_center' => 1,
		'icon'        => 1,
		'cookie'      => '',
		'cookie_time' => 30,
		'only_guests' => false
	], $args, 'usp-notice' );

	if ( empty( $atts['text'] ) ) {
		return false;
	}

	if ( $atts['only_guests'] && is_user_logged_in() ) {
		return false;
	}

	$argum = [];

	if ( in_array( $atts['type'], [ 'info', 'success', 'warning', 'error', 'simple' ], true ) ) {
		$argum['type'] = $atts['type'];
	}

	if ( $atts['title'] ) {
		$argum['title'] = wp_kses_data( $atts['title'] );
	}

	if ( ! empty( $atts['cookie'] ) ) {
		$argum['cookie'] = sanitize_key( $atts['cookie'] );
	}

	$argum['text'] = wp_kses_data( $atts['text'] );

	$argum['text_center'] = (bool) $atts['text_center'];

	if ( 1 == $atts['icon'] ) {
		$argum['icon'] = true;
	} else if ( is_string( $atts['icon'] ) ) {
		$argum['icon'] = sanitize_title( $atts['icon'] );
	}

	return usp_get_notice( $argum );
}
