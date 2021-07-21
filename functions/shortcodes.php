<?php

/**
 * Displays the user's personal account
 *
 * And if the output of the personal account in the settings is selected via author.php
 * - then displays the correct link to go to the user's account
 *
 * @return string   HTML content to display personal account.
 * @since 1.0.0
 *
 */
add_shortcode( 'userspace', 'usp_get_userspace' );
function usp_get_userspace() {

	if ( USP()->office()->on_page() && empty( USP()->office()->get_var( 'member' ) ) ) {
		return usp_get_variations_buttons();
	}

	ob_start();

	userspace();

	$content = ob_get_contents();
	ob_end_clean();

	return $content;
}

function usp_get_variations_buttons() {
	if ( is_user_logged_in() ) {
		global $user_ID;

		$args = [
			'label' => __( 'Go to personal account', 'userspace' ),
			'icon'  => 'fa-user',
			'size'  => 'medium',
			'href'  => usp_user_get_url( $user_ID )
		];

		return usp_get_button( $args );
	} else {
		return usp_get_user_widget();
	}
}

/**
 * Displays the logged in user control panel and login buttons to guests
 *
 * If it is not logged in, it will display the login and registration buttons.
 * If logged in, it displays the avatar of the current user and the buttons to go to the personal account and exit the site
 *
 * @return string   HTML content to display control panel.
 * @since 1.0.0
 *
 */
add_shortcode( 'usp-user-widget', 'usp_get_user_widget' );
function usp_get_user_widget() {
	$buttons = [];

	$content = '<div class="usp-user-widget usps">';

	if ( is_user_logged_in() ) {
		global $user_ID;

		$avatar = usp_get_avatar( $user_ID, 100, false, [ 'class' => 'usps__fit-cover' ] );

		$userContent = apply_filters( 'usp_widget_userdata_content', $avatar );

		if ( $userContent ) {
			$content .= '<div class="usp-user-widget__left">' . $userContent . '</div>';
		}

		$buttons[] = [
			'label' => __( 'My account', 'userspace' ),
			'icon'  => 'fa-home',
			'size'  => 'medium',
			'href'  => usp_user_get_url( $user_ID )
		];

		$buttons[] = [
			'label' => __( 'Exit', 'userspace' ),
			'href'  => wp_logout_url( home_url() ),
			'icon'  => 'fa-external-link',
			'size'  => 'medium',
		];
	} else {

		//use loginform module
		if ( ! usp_get_option( 'usp_login_form' ) ) {
			usp_dialog_scripts();
		}

		$buttons[] = [
			'label'   => __( 'Sign in', 'userspace' ),
			'icon'    => 'fa-sign-in',
			'size'    => 'medium',
			'onclick' => usp_get_option( 'usp_login_form' ) ? null : 'USP.loginform.call("login");return false;',
			'href'    => usp_get_loginform_url( 'login' ),
			'class'   => 'usp-entry-bttn'
		];

		$buttons[] = [
			'label'   => __( 'Register', 'userspace' ),
			'icon'    => 'fa-book',
			'size'    => 'medium',
			'onclick' => usp_get_option( 'usp_login_form' ) ? null : 'USP.loginform.call("register");return false;',
			'href'    => usp_get_loginform_url( 'register' ),
			'class'   => 'usp-entry-bttn'
		];
	}

	$all_buttons = apply_filters( 'usp_widget_buttons', $buttons );

	if ( $all_buttons ) {

		$content .= '<div class="usp-user-widget__right usps usps__column">';

		foreach ( $all_buttons as $button ) {
			$content .= usp_get_button( $button );
		}

		$content .= '</div>';
	}

	$content .= '</div>';

	return $content;
}

/**
 * Displays login, registration, and reset password forms
 * If logged in - displays the button to go to the personal account
 *
 * @param   array  $atts                    $atts['active']     Set active tab in form.
 *                                          Default: 'login'.
 *                                          Available: login|register|lostpassword
 *                                          $atts['forms']      What forms to display in tabs
 *                                          Default: login,register,lostpassword
 *
 * @return string       HTML content to display loginform.
 * @since 1.0.0
 *
 */
add_shortcode( 'usp-loginform', 'usp_get_loginform_shortcode' );
function usp_get_loginform_shortcode( $atts = [] ) {
	if ( is_user_logged_in() ) {
		global $user_ID;

		$url = '<a href="' . usp_user_get_url( $user_ID ) . '">' . __( 'personal account', 'userspace' ) . '</a>';

		return usp_get_notice( [
			'type' => 'success',
			'text' => sprintf( __( 'You are already logged into the site. Go to your %s, to get started.', 'userspace' ), $url )
		] );
	}

	//use module loginform
	return usp_get_loginform( $atts );
}

/**
 * Displays registered users
 *
 * @param   array  $atts
 * $atts['number']
 * $atts['orderby']
 * $atts['order']
 * $atts['template']
 *
 * @return string       HTML content to display userlist.
 * @since 1.0.0
 *
 */
add_shortcode( 'usp-users', 'usp_get_users' );
function usp_get_users( $atts = [] ) {

	USP()->use_module( 'users-list-new' );

	$manager = new USP_Users_Manager( $atts );

	$content = '<div class="usp-users-list">';
	$content .= $manager->get_manager();
	$content .= '</div>';

	return $content;

}

/**
 * Displays registered users
 *
 * @param   array  $atts                        $atts['inpage']         Set users per page
 *                                              Default: 30
 *                                              $atts['number']         Maximum number users
 *                                              $atts['template']       User output template
 *                                              Available: rows|masonry|avatars|mini
 *                                              Default: rows
 *                                              $atts['search_form']    Search form on top
 *                                              Available: 0|1
 *                                              Default: 1
 *                                              $atts['filters']        Filter buttons on top
 *                                              Available: 0|1
 *                                              Default: 0
 *                                              $atts['orderby']        Order by: time_action (last online)
 *                                              Available: posts_count|comments_count|display_name|user_registered|time_action
 *                                              Default: time_action
 *                                              $atts['order']          Sorting direction. ASC|DESC
 *                                              Default: DESC
 *                                              $atts['data']           Output additional data comma-separated list (if template supports)
 *                                              Available: posts_count,comments_count,description,user_registered,profile_fields
 *                                              Default: empty
 *                                              $atts['exclude']        Exclude user by ID. Comma-separated numbers
 *                                              $atts['include']        Show only by ID
 *                                              $atts['usergroup']      Show by metakey;
 *                                              for example:        usergroup="meta_key_1:value_1"
 *                                              or multiple values  usergroup="meta_key_1:value_1|meta_key_2:value_2"
 *                                              $atts['only']           Set 'action_users' and see who online
 *
 * @return string       HTML content to display userlist.
 * @since 1.0.0
 *
 */
add_shortcode( 'usp-userlist', 'usp_get_userlist' );
function usp_get_userlist( $atts = [] ) {
	global $usp_user, $usp_users_set, $user_ID;

	USP()->use_module( 'users-list' );

	$users = new USP_Users_List( $atts );

	$count_users = false;

	if ( ! isset( $atts['number'] ) ) {

		$count_users = $users->get_count();

		$pagenavi = new USP_Pager( array(
			'total'  => $count_users,
			'number' => $users->query['number'],
			'class'  => 'usp-users__nav',
		) );

		$users->query['offset'] = $pagenavi->offset;
	}

	$timecache = ( $user_ID && $users->query['number'] == 'time_action' ) ? usp_get_option( 'usp_user_timeout', 600 ) : 0;

	$usp_cache = new USP_Cache( $timecache );

	if ( $usp_cache->is_cache ) {
		if ( isset( $users->id ) && $users->id == 'usp-online-users' ) {
			$string = json_encode( $users );
		} else {
			$string = json_encode( $users->query );
		}

		$file = $usp_cache->get_file( $string );

		if ( ! $file->need_update ) {

			$users->remove_filters();

			return $usp_cache->get_cache();
		}
	}

	$usersdata = $users->get_users();

	$userlist = $users->get_filters( $count_users );

	if ( ! $usersdata ) {
		$userlist .= usp_get_notice( [ 'text' => __( 'Users not found', 'userspace' ) ] );
	} else {

		if ( ! isset( $atts['number'] ) && $pagenavi->number ) {
			$userlist .= $pagenavi->get_navi();
		}

		$data_masonry = ( $users->template === 'masonry' ) ? 'data-columns' : '';

		$userlist .= '<div class="usp-users__list usps usp-users__' . $users->template . '" ' . $data_masonry . '>';

		$usp_users_set = $users;

		foreach ( $usersdata as $usp_user ) {
			$users->setup_userdata( $usp_user );

			$userlist .= usp_get_include_template( 'usp-user-' . $users->template . '.php' );
		}

		$userlist .= '</div>';

		if ( ! isset( $atts['number'] ) && $pagenavi->number ) {
			$userlist .= $pagenavi->get_navi();
		}
	}

	$users->remove_filters();

	if ( $usp_cache->is_cache ) {
		$usp_cache->update_cache( $userlist );
	}

	return '<div class="usp-users">' . $userlist . '</div>';
}

/**
 * Allows you to cache the content.
 * As a rule, these are other shortcodes
 * Example: [usp-cache time="3600" key="my-unique-key"]say hello shortcode[/usp-cache]
 *
 * @param   array  $atts  $atts['time']       Caching time in seconds.
 *                        $atts['key']        String, unique key
 *                        $atts['only_guest'] Cached for guests only
 *
 * @return string       HTML cached content.
 * @since 1.0.0
 *
 */
add_shortcode( 'usp-cache', 'usp_cache_shortcode' );
function usp_cache_shortcode( $atts, $content_in = null ) {
	global $post;

	$attr = shortcode_atts( array(
		'key'        => '',
		'only_guest' => false,
		'time'       => false
	), $atts );

	if ( $post->post_status == 'publish' ) {

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

	if ( $post->post_status == 'publish' ) {

		if ( $usp_cache->is_cache ) {
			$usp_cache->update_cache( $content );
		}
	}

	return $content;
}

/**
 * Shortcode of the exit button from the site
 * The button is shown only to logged-in users
 *
 * @param   array  $attr                    $attr['text']       Text on button
 *                                          $atts['redirect']   Redirect after logout.
 *                                          Default: 'home'
 *                                          Available: home|current
 *                                          $atts['type']       Type of button
 *                                          Default: 'primary'
 *                                          Available: primary|simple|clear
 *
 * @return string       HTML logout button.
 * @since 1.0.0
 *
 */
add_shortcode( 'usp-logout', 'usp_logout_shortcode' );
function usp_logout_shortcode( $attr ) {
	// the user not logged in to the site
	if ( ! is_user_logged_in() ) {
		return;
	}

	$atts = shortcode_atts( [
		'text'     => __( 'Log Out', 'userspace' ),
		'redirect' => 'home',
		'type'     => 'primary',
	], $attr, 'usp-logout' );

	return usp_logout_button( $atts );
}

function usp_logout_button( $atts ) {
	$url = get_home_url();

	if ( $atts['redirect'] == 'current' ) {
		$url = get_the_permalink();
	}

	$args = [
		'label' => $atts['text'],
		'type'  => $atts['type'],
		'size'  => 'medium',
		'href'  => esc_url( wp_logout_url( $url ) )
	];

	return usp_get_button( $args );
}

/**
 * Shortcode displays notice box
 *
 * @param   array  $args  Extra options.
 *                        $args['text']           Required. Text message
 *                        $args['title']          Title text
 *                        $args['type']           Type notice. Default: info. Allowed: info|success|warning|error|simple
 *                        $args['text_center']    1 - text-align:center; 0 - left text position. Default 1
 *                        $args['icon']           left position icon; 0 - don't show, string - icon class. Example: 'fa-info'. Default 1
 *
 * @return string   HTML notice box.
 * @since 1.0
 *
 */
add_shortcode( 'usp-notice', 'usp_notice_shortcode' );
function usp_notice_shortcode( $args ) {
	$atts = shortcode_atts( array(
		'type'        => 'info',
		'title'       => false,
		'text'        => '',
		'text_center' => 1,
		'icon'        => 1,
	), $args, 'usp-notice' );

	if ( empty( $atts['text'] ) ) {
		return;
	}

	$argum = [];

	if ( in_array( $atts['type'], [ 'info', 'success', 'warning', 'error', 'simple' ] ) ) {
		$argum['type'] = $atts['type'];
	}

	if ( $atts['title'] ) {
		$argum['title'] = wp_kses_data( $atts['title'] );
	}

	$argum['text'] = wp_kses_data( $atts['text'] );

	$argum['text_center'] = ( $atts['text_center'] ) ? true : false;

	if ( $atts['icon'] == 1 ) {
		$argum['icon'] = true;
	} else if ( is_string( $atts['icon'] ) ) {
		$argum['icon'] = sanitize_title( $atts['icon'] );
	}

	return usp_get_notice( $argum );
}
