<?php

class USP_User {

	public $ID;
	public $metadata = [];

	function __construct( $user_id ) {

		$this->ID = $user_id;
	}

	function setup( $userObject ) {

		if ( ! $userObject ) {
			return $this;
		}

		foreach ( $userObject as $key => $value ) {

			if ( $key == 'metadata' ) {

				$this->$key = array_merge( $this->$key, $value );

				continue;
			}

			$this->$key = $value;
		}

		return $this;
	}

	function get_url() {

		$officeUrl = get_permalink( usp_get_option( 'account_page' ) );

		if ( '' == get_site_option( 'permalink_structure' ) ) {

			$officeUrl = add_query_arg(
				[
					'user' => $this->ID
				], $officeUrl );
		} else {
			$userData  = get_userdata( $this->ID );
			$officeUrl = untrailingslashit( $officeUrl ) . '/' . $userData->user_nicename;
		}

		return trailingslashit( $officeUrl );
	}

	/**
	 * @return string mysql datetime last action
	 */
	function get_last_action() {

		$cachekey = md5( "usp_user_{$this->ID}_last_action" );
		$cache    = wp_cache_get( $cachekey, 'usp_users' );

		if ( $cache !== false ) {
			return $cache;
		}

		if ( isset( $this->last_activity ) ) {
			$action = $this->last_activity;
		} else {
			$action = ( new USP_User_Action() )->select( [ 'date_action' ] )->where( [ 'user_id' => $this->ID ] )->get_var();
		}

		wp_cache_set( $cachekey, $action ?: '', 'usp_users', usp_get_option( 'usp_user_timeout', 10 ) * 60 );

		return $action ?: '';
	}

	/**
	 * @return bool
	 */
	function is_online() {

		$last_action = $this->get_last_action();

		if ( ! $last_action ) {
			return false;
		}

		$last_action_timestamp = strtotime( $last_action );

		$timeout = usp_get_option( 'usp_user_timeout', 10 ) * 60;

		return current_time( 'timestamp' ) - $last_action_timestamp <= $timeout;
	}

	/**
	 * @return string 'online' or 'offline'
	 */
	function get_action_status() {

		return $this->is_online() ? __( 'online', 'userspace' ) : __( 'offline', 'userspace' );
	}

	/**
	 * @return string how long user offline
	 */
	function get_offline_diff() {

		$last_action = $this->get_last_action();

		if ( ! $last_action ) {
			return __( 'long ago', 'userspace' );
		}

		return human_time_diff( strtotime( $last_action ), current_time( 'timestamp' ) );
	}

	/**
	 * @return string html of user action status
	 */
	function get_action_html() {

		$is_online     = $this->is_online();
		$action_status = $this->get_action_status();
		$class         = $is_online ? 'usp-online' : 'usp-offline';

		if ( ! $is_online ) {
			$action_status .= ' ' . $this->get_offline_diff();
		}

		$html = sprintf( '<span class="usp-status-user %s">%s</span>', $class, $action_status );

		return apply_filters( 'usp_user_action_html', $html, $is_online, $this );
	}

	/**
	 * @return string html icon
	 */
	function get_action_icon() {

		$is_online     = $this->is_online();
		$action_status = $this->get_action_status();
		$class         = $is_online ? 'usp-online' : 'usp-offline';

		if ( ! $is_online ) {
			$action_status .= ' ' . $this->get_offline_diff();
		}

		$icon = sprintf( '<i class="uspi fa-circle usp-status-user %s" title="%s"></i>', $class, $action_status );

		return apply_filters( 'usp_user_action_icon', $icon, $is_online, $this );
	}

	/**
	 * Get username
	 *
	 * @param string $link Return a name with a link to the specified url
	 *                              Default 'false'.
	 * @param array $args {
	 *                              Optional. Extra arguments to retrieve username link.
	 *
	 * @type array|string $class Array or string of additional classes to add to the img element.
	 * }
	 *
	 * @return string|bool  username or 'false' - if the user for this id does not exist
	 * @since 1.0
	 *
	 */
	function get_username( $link = false, $args = false ) {

		if ( isset( $this->display_name ) ) {
			$username = $this->display_name;
		} else {
			$userdata = get_userdata( $this->ID );
			$username = $userdata->display_name ?: $userdata->user_login;
		}

		if ( $link ) {

			$class = [ 'usp_userlink' ];

			if ( isset( $args['class'] ) ) {

				$class = array_merge( $class, (array) $args['class'] );
			}

			$username = '<a class="' . esc_attr( implode( ' ', $class ) ) . '" href="' . $link . '" rel="nofollow">' . $username . '</a>';
		}

		return apply_filters( 'usp_user_username', $username, $link, $args, $this );
	}

	function get_age() {

		$bip_birthday = get_user_meta( $user_id, 'usp_birthday', true );

		// there is no data
		if ( ! $bip_birthday ) {
			return false;
		}

		return date_diff( date_create( $bip_birthday ), date_create( 'today' ) )->y;
	}

	function is_role( $role ) {

	}

	function is_access_console() {

	}

	function get_cover_url() {

	}

	/**
	 * @param string $action_time mysql datetime of last activity
	 * @param bool $force_update
	 *
	 * @return void
	 */
	function update_activity( $action_time = '', $force_update = false ) {

		if ( ! $force_update && $this->is_online() ) {
			return;
		}

		$action_time = $action_time ?: current_time( 'mysql' );

		$last_action = $this->get_last_action();

		if ( $last_action ) {

			USP_Query::update( ( new USP_User_Action() )->where( [
				'user_id' => $this->ID
			] ), [
				'date_action' => $action_time
			] );

		} else {

			USP_Query::insert( new USP_User_Action(), [
				'user_id'     => $this->ID,
				'date_action' => $action_time
			] );

		}

		$cachekey = md5( "usp_user_{$this->ID}_last_action" );
		wp_cache_set( $cachekey, $action_time, 'usp_users', usp_get_option( 'usp_user_timeout', 10 ) * 60 );

		do_action( 'usp_user_update_activity', $this );
	}

	function __get( $property ) {

		if ( isset( $this->$property ) ) {
			return $this->$property;
		}

		if ( isset( $this->metadata[ $property ] ) ) {
			return $this->metadata[ $property ];
		}

		return get_user_meta( $this->ID, $property, true );

	}
}
