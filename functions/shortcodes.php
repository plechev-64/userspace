<?php

add_shortcode( 'userspace', 'usp_get_userspace' );
function usp_get_userspace() {
	global $user_LK;

	if ( ! $user_LK ) {
		return usp_get_userpanel();
	}

	ob_start();

	userspace();

	$content = ob_get_contents();
	ob_end_clean();

	return $content;
}

add_shortcode( 'usp-user-widget', 'usp_get_user_widget' );
function usp_get_user_widget( $atts = [ ] ) {
	global $user_ID;

	$buttons = [ ];

	$content = '<div class="usp-user-widget">';

	if ( $user_ID ) {

		$userContent = apply_filters( 'usp_widget_userdata_content', get_avatar( $user_ID, 100 ) );

		if ( $userContent ) {

			$content .= '<div class="userdata">';

			$content .= $userContent;

			$content .= '</div>';
		}

		$buttons[] = [
			'label'	 => __( 'Личный кабинет', 'usp' ),
			'icon'	 => 'fa-home',
			'size'	 => 'medium',
			'href'	 => usp_get_user_url( $user_ID )
		];

		$buttons[] = [
			'label'	 => __( 'Exit', 'usp' ),
			'href'	 => wp_logout_url( home_url() ),
			'icon'	 => 'fa-external-link',
			'size'	 => 'medium',
		];
	} else {

		//use loginform module
		if ( ! usp_get_option( 'login_form_recall' ) )
			usp_dialog_scripts();

		$buttons[] = [
			'label'		 => __( 'Авторизация', 'usp' ),
			'icon'		 => 'fa-sign-in',
			'size'		 => 'medium',
			'onclick'	 => usp_get_option( 'login_form_recall' ) ? null : 'USP.loginform.call("login");return false;',
			'href'		 => usp_get_loginform_url( 'login' )
		];

		$buttons[] = [
			'label'		 => __( 'Регистрация', 'usp' ),
			'icon'		 => 'fa-book',
			'size'		 => 'medium',
			'onclick'	 => usp_get_option( 'login_form_recall' ) ? null : 'USP.loginform.call("register");return false;',
			'href'		 => usp_get_loginform_url( 'register' )
		];
	}

	$buttons = apply_filters( 'usp_widget_buttons', $buttons );

	if ( $buttons ) {

		$content .= '<div class="buttons usp-wrap usp-wrap_vertical">';

		foreach ( $buttons as $button ) {
			$content .= usp_get_button( $button );
		}

		$content .= '</div>';
	}

	$content .= '</div>';

	return $content;
}

add_shortcode( 'loginform', 'usp_get_loginform_shortcode' );
function usp_get_loginform_shortcode( $atts = [ ] ) {
	global $user_ID;

	if ( $user_ID ) {
		return usp_get_notice( [
			'type'	 => 'success',
			'text'	 => __( 'Вы уже авторизованы на сайте. Перейдите в <a href="' . usp_get_user_url( $user_ID ) . '">личный кабинет</a>, чтобы начать работу.', 'usp' )
			] );
	}

	//use module loginform
	return usp_get_loginform( $atts );
}

add_shortcode( 'userlist', 'usp_get_userlist' );
function usp_get_userlist( $atts = [ ] ) {
	global $usp_user, $usp_users_set, $user_ID;

	require_once USP_PATH . 'classes/class-usp-users-list.php';

	$users = new USP_Users_List( $atts );

	$count_users = false;

	if ( ! isset( $atts['number'] ) ) {

		$count_users = $users->count();

		$id_pager = ($users->id) ? 'usp-users-' . $users->id : 'usp-users';

		$pagenavi = new USP_PageNavi( $id_pager, $count_users, array( 'in_page' => $users->query['number'] ) );

		$users->query['offset'] = $pagenavi->offset;
	}

	$timecache = ($user_ID && $users->query['number'] == 'time_action') ? usp_get_option( 'timeout', 600 ) : 0;

	$usp_cache = new USP_Cache( $timecache );

	if ( $usp_cache->is_cache ) {
		if ( isset( $users->id ) && $users->id == 'usp-online-users' )
			$string	 = json_encode( $users );
		else
			$string	 = json_encode( $users->query );

		$file = $usp_cache->get_file( $string );

		if ( ! $file->need_update ) {

			$users->remove_filters();

			return $usp_cache->get_cache();
		}
	}

	$usersdata = $users->get_users();

	$userlist = $users->get_filters( $count_users );

	$userlist .= '<div class="usp-userlist">';

	if ( ! $usersdata ) {
		$userlist .= usp_get_notice( ['text' => __( 'Users not found', 'usp' ) ] );
	} else {

		if ( ! isset( $atts['number'] ) && $pagenavi->in_page ) {
			$userlist .= $pagenavi->pagenavi();
		}

		$userlist .= '<div class="userlist ' . $users->template . '-list">';

		$usp_users_set = $users;

		foreach ( $usersdata as $usp_user ) {
			$users->setup_userdata( $usp_user );
			$userlist .= usp_get_include_template( 'user-' . $users->template . '.php' );
		}

		$userlist .= '</div>';

		if ( ! isset( $atts['number'] ) && $pagenavi->in_page ) {
			$userlist .= $pagenavi->pagenavi();
		}
	}

	$userlist .= '</div>';

	$users->remove_filters();

	if ( $usp_cache->is_cache ) {
		$usp_cache->update_cache( $userlist );
	}

	return $userlist;
}

add_shortcode( 'usp-cache', 'usp_cache_shortcode' );
function usp_cache_shortcode( $atts, $content = null ) {
	global $post;

	extract( shortcode_atts( array(
		'key'		 => '',
		'only_guest' => false,
		'time'		 => false
			), $atts ) );

	if ( $post->post_status == 'publish' ) {

		$key = '-cache-' . $post->ID;

		$usp_cache = new USP_Cache( $time, $only_guest );

		if ( $usp_cache->is_cache ) {

			$file = $usp_cache->get_file( $key );

			if ( ! $file->need_update ) {
				return $usp_cache->get_cache();
			}
		}
	}

	$content = do_shortcode( shortcode_unautop( $content ) );
	if ( '</p>' == substr( $content, 0, 4 )
		and '<p>' == substr( $content, strlen( $content ) - 3 ) )
		$content = substr( $content, 4, strlen( $content ) - 7 );

	if ( $post->post_status == 'publish' ) {

		if ( $usp_cache->is_cache ) {
			$usp_cache->update_cache( $content );
		}
	}

	return $content;
}
