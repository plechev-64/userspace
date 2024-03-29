<?php

class USP_User {

	public $ID;
	public $metadata = [];

	private $_profile_fields = null;

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

	function profile_fields() {

		USP()->use_module( 'profile-fields' );

		if ( is_null( $this->_profile_fields ) ) {
			$this->_profile_fields = new USP_User_Profile_Fields( $this );
		}

		return $this->_profile_fields;
	}

	function get_url( $tab = null, $subtab = null ) {

		$officeUrl = get_permalink( usp_get_option( 'account_page' ) );

		if ( '' == get_site_option( 'permalink_structure' ) ) {
			$officeUrl = add_query_arg( [ 'user' => $this->ID, 'tab' => $tab, 'subtab' => $subtab ], $officeUrl );
		} else {
			$officeUrl = trailingslashit( trailingslashit( $officeUrl ) . $this->user_nicename );
			$officeUrl = add_query_arg( [ 'tab' => $tab, 'subtab' => $subtab ], $officeUrl );
		}

		return $officeUrl;
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
		$action_status = $this->get_action( 'text' );
		$class         = $is_online ? 'usp-online' : 'usp-offline';

		if ( ! $is_online ) {
			$action_status .= ' ' . $this->get_offline_diff();
		}

		$html = sprintf( '<span class="usp-status-user usps__line-1 %s">%s</span>', $class, $action_status );

		return apply_filters( 'usp_user_action_html', $html, $is_online, $this );
	}

	/**
	 * @return string html icon
	 */
	function get_action_icon() {

		$is_online     = $this->is_online();
		$action_status = $this->get_action( 'text' );
		$class         = $is_online ? 'usp-online' : 'usp-offline';

		if ( ! $is_online ) {
			$action_status .= ' ' . $this->get_offline_diff();
		}

		$icon = sprintf( '<i class="uspi fa-circle usp-status-user usps__line-1 %s" title="%s"></i>', $class, $action_status );

		return apply_filters( 'usp_user_action_icon', $icon, $is_online, $this );
	}

	/**
	 * @param string $type html|icon|mixed|text
	 *
	 * @return string
	 */
	function get_action( $type = 'html' ) {

		switch ( $type ) {
			case 'html' :
				$action = $this->get_action_html();
				break;
			case 'icon' :
				$action = $this->get_action_icon();
				break;
			case 'mixed' :
				$action = $this->is_online() ? $this->get_action_icon() : $this->get_action_html();
				break;
			case 'text' :
				$action = $this->is_online() ? __( 'online', 'userspace' ) : __( 'offline', 'userspace' );
				break;
			default:
				$action = $this->get_action_html();
		}

		return $action;
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

		$username = $this->display_name ?: $this->user_login;

		if ( $link ) {

			$class = [ 'usp_userlink' ];

			if ( isset( $args['class'] ) ) {

				$class = array_merge( $class, (array) $args['class'] );
			}

			$username = '<a class="' . esc_attr( implode( ' ', $class ) ) . '" href="' . $link . '" rel="nofollow">' . $username . '</a>';
		}

		return apply_filters( 'usp_user_username', $username, $link, $args, $this );
	}

	/**
	 * @return string user birthday date
	 */
	function get_birthday_date() {

		return $this->usp_birthday;
	}

	/**
	 * @return string user sex
	 */
	function get_user_sex() {
		return $this->usp_sex;
	}

	/**
	 * @return string user date registered
	 */
	function get_user_registered() {
		return mysql2date( 'd-m-Y', $this->user_registered );
	}

	/**
	 * @return string counts the number of days on the site after registration
	 */
	function get_user_count_days_after_registered() {
		$t_day = get_date_from_gmt( date( 'Y-m-d H:i:s' ), 'Y-m-d' );
		$d_m_y = mysql2date( 'Y-m-d', $this->user_registered );

		// date difference
		$d_register = date_create( $d_m_y );
		$d_current  = date_create( $t_day );
		$interval   = date_diff( $d_register, $d_current );

		return $interval->days;
	}

	/**
	 * @return string count of comments
	 */
	function get_count_comments() {
		global $wpdb;

		return $wpdb->get_var( "SELECT COUNT(comment_ID) FROM " . $wpdb->comments . " WHERE user_id = " . $this->ID . " AND comment_approved = 1" );
	}

	/**
	 * @return object counts by post types
	 */
	function get_count_posts_by_types() {
		global $wpdb;

		return $wpdb->get_results( "SELECT post_type,count(*) AS count 
											FROM " . $wpdb->posts . " 
											WHERE post_author = " . $this->ID . " 
											AND post_status = 'publish' 
											GROUP BY post_type", OBJECT_K );
	}

	/**
	 * @return false|int user age or false if birthday not exist
	 */
	function get_age() {

		$birthday = $this->get_birthday_date();

		if ( ! $birthday ) {
			return false;
		}

		return date_diff( date_create( $birthday ), date_create( 'today' ) )->y;
	}

	/**
	 * @param string $class additional class.
	 *
	 * @return string   html box with user age
	 */
	function get_age_html( $class = '' ) {

		$age = $this->get_age();

		if ( $age ) {
			return '<div class="usp-age ' . $class . '">' . sprintf( _n( '%s year', '%s years', $age, 'userspace' ), $age ) . '</div>';
		}

		return '';
	}

	/**
	 * @return string user description
	 */
	function get_description() {
		return $this->description;
	}

	/**
	 * @param array $attr $attr['side'] left|top (default: left)
	 *                           $attr['class'] additional css class
	 *
	 * @return string user description html block
	 */
	function get_description_html( $attr = [] ) {

		$description = $this->get_description();

		if ( ! $description ) {
			return '';
		}

		$attr = wp_parse_args( $attr, [ 'side' => 'left', 'class' => '' ] );

		$description = nl2br( wp_strip_all_tags( $description ) );
		$class       = $attr['class'] ? $attr['class'] . ' ' : '';
		$side        = 'usp-descr-' . $attr['side'];

		$html = '<div class="' . $class . 'usp-descr-wrap usps ' . $side . '">'
		        . '<div class="usp-descr usps__relative usps__radius-3">' . $description . '</div>'
		        . '</div>';

		return apply_filters( 'usp_user_description_html', $html, $attr, $this );
	}

	/**
	 * @param string|array $role
	 *
	 * @return bool
	 */
	function has_role( $role ) {

		$need_roles = (array) $role;

		$userdata = get_userdata( $this->ID );

		foreach ( $userdata->roles as $user_role ) {
			if ( in_array( $user_role, $need_roles ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return bool can user access to console
	 */
	function is_access_console() {

		$access_roles   = (array) usp_get_option( 'usp_console_access', [] );
		$access_roles[] = 'administrator';

		return $this->has_role( $access_roles );
	}

	/**
	 * Check if isset cover
	 *
	 * @return false|int    int - ID uploaded cover | false - if the cover has not been uploaded
	 */
	function is_cover() {
		return $this->usp_cover ?: false;
	}

	function get_cover_url( $avatar_as_cover = false ) {

		$cover_id = $this->is_cover() ?: usp_get_option( 'usp_default_cover', 0 );

		if ( $cover_id ) {
			return wp_get_attachment_image_url( $cover_id, 'large' );
		}

		return usp_get_default_cover( $avatar_as_cover, $this->ID );
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

	function get_avatar( $size = 50, $url = false, $args = [], $html = false ) {

		$alt = ( isset( $args['parent_alt'] ) ) ? $args['parent_alt'] : '';

		// class for avatar userspace and class css reset for <img> tag
		( isset( $args['class'] ) ) ? $args['class'] .= ' usp-ava-img usps__img-reset' : $args['class'] = 'usp-ava-img usps__img-reset';

		// class for current user (realtime reload on avatar upload)
		if ( $this->ID == get_current_user_id() ) {
			$args['class'] .= ' usp-profile-ava';
		}

		if ( $url || isset( $args['parent_wrap'] ) && $args['parent_wrap'] == 'div' ) {

			$wrap_tag = ( ! isset( $args['parent_wrap'] ) || $args['parent_wrap'] == 'a' ) ? 'a' : 'div';
			$id       = ( isset( $args['parent_id'] ) ) ? 'id="' . esc_attr( $args['parent_id'] ) . '"' : '';
			$class    = ( isset( $args['parent_class'] ) ) ? 'class="' . esc_attr( $args['parent_class'] ) . '"' : '';
			$title    = ( isset( $args['parent_title'] ) ) ? 'title="' . esc_attr( $args['parent_title'] ) . '"' : '';
			$onclick  = ( isset( $args['parent_onclick'] ) ) ? 'onclick="' . esc_attr( $args['parent_onclick'] ) . '"' : '';
			$href     = ( $url ) ? 'href="' . esc_url( $url ) . '"' : '';
			$nofollow = ( $wrap_tag == 'a' ) ? 'rel="nofollow"' : '';

			$parent_tag = sprintf( "<{$wrap_tag} %s %s %s %s %s %s>", $id, $class, $href, $title, $onclick, $nofollow );

			$parent_tag .= ! empty( $this->avatar ) ? $this->avatar->get_image( [
				$size,
				$size
			], $args ) : get_avatar( $this->ID, $size, false, $alt, $args );

			// some html or apply_filters
			if ( isset( $html ) ) {
				$parent_tag .= $html;
			}

			$parent_tag .= "</{$wrap_tag}>";

			return $parent_tag;
		}

		return ! empty( $this->avatar ) ? $this->avatar->get_image( [
			$size,
			$size
		], $args ) : get_avatar( $this->ID, $size, false, $alt, $args );
	}

	/**
	 * Add user to blacklist
	 *
	 * @param int $user_id
	 *
	 * @return bool
	 */
	function block( int $user_id ) {

		if ( ! $user_id || $this->is_blocked( $user_id ) ) {
			return false;
		}

		$result = (bool) USP_Query::insert( new USP_Blacklist_Query(), [
			'user_id' => $this->ID,
			'blocked' => $user_id
		] );

		if ( $result ) {
			/**
			 * Fires after adding a user to the blacklist.
			 *
			 * @param   $user_id    int ID user.
			 *
			 * @since   1.0.0
			 *
			 */
			do_action( 'usp_user_block', $user_id, $this );
		}

		return $result;

	}

	/**
	 * Remove user from blacklist
	 *
	 * @param int $user_id
	 *
	 * @return bool
	 */
	function unblock( int $user_id ) {

		if ( ! $user_id || ! $this->is_blocked( $user_id ) ) {
			return false;
		}

		$result = USP_Query::delete(
			( new USP_Blacklist_Query() )->where( [ 'user_id' => $this->ID, 'blocked' => $user_id ] )
		);

		if ( $result ) {
			/**
			 * Fires after remove user from blacklist.
			 *
			 * @param   $user_id    int ID user.
			 *
			 * @since   1.0.0
			 *
			 */
			do_action( 'usp_user_unblock', $user_id, $this );
		}

		return $result;

	}

	/**
	 * Check if user is blocked
	 *
	 * @param int $user_id
	 *
	 * @return bool
	 */
	function is_blocked( int $user_id ) {

		return (bool) ( new USP_Blacklist_Query() )
			->select( [ 'ID' ] )
			->where( [ 'user_id' => $this->ID, 'blocked' => $user_id ] )
			->get_var();

	}

	/**
	 * User blacklist
	 *
	 * Returns the user's blacklist
	 *
	 * @return array
	 */
	function get_blacklist() {

		return ( new USP_Blacklist_Query() )
			->select( [ 'blocked' ] )
			->where( [ 'user_id' => $this->ID ] )
			->limit( - 1 )
			->get_col( [ 'blocked' ] );

	}

	function __get( $property ) {

		if ( isset( $this->$property ) ) {
			return $this->$property;
		}

		if ( isset( $this->metadata[ $property ] ) ) {
			return $this->metadata[ $property ];
		}

		return get_userdata( $this->ID )->$property;

	}
}
