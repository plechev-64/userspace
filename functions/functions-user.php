<?php /** @noinspection PhpMissingReturnTypeInspection */
/** @noinspection PhpMissingParamTypeInspection */
/** @noinspection PhpUnused */

/**
 * Retrieve the avatar `<img>` tag for a user
 * wraps it, if necessary, in the parent tag `<a>` or `<div>`
 *
 * @param   $user_id    int     User ID
 * @param   $size       int     Optional. Height and width of the avatar image file in pixels.
 *                              Default: 50.
 * @param   $url        string  Optional. URL for the parent wrapper.
 *                              Or `#` if used $args['parent_onclick']
 *
 * @param   $args       array   Optional. Extra arguments to retrieve the avatar:
 * <pre>
 * $args['parent_wrap'] string  The `img` tag to wrap the parent tag.
 *                              Available: `a` or `div`.
 *                              Default: `a`
 * $args['parent_id']       string  ID of the parent tag.
 * $args['parent_class']    string  Class of the parent tag.
 * $args['parent_title']    string  Title of the parent tag.
 * $args['parent_onclick']  string  Onclick of the parent tag (set $url as `#`)
 * $args['alt']             string  Alternative text to use in img tag.
 *                                  Default: empty.
 * $args['height']          int     Display height of the avatar in pixels.
 *                                  Default: $size.
 * $args['width']           int     Display width of the avatar in pixels.
 *                                  Default: $size.
 * $args['force_default']   bool    Whether to always show the default image, never the Gravatar.
 *                                  Default: false.
 * $args['rating']          string  What rating to display avatars up to.
 *                                  Available: 'G', 'PG', 'R', 'X', and are judged in that order.
 *                                  Default: is the value of the 'avatar_rating' option.
 * $args['scheme']          string  URL schemes to use. See set_url_scheme() for accepted values.
 *                                  Default: null.
 * $args['class']   array|string    Array or string of additional classes to add to the img element.
 *                                  Default: null.
 * $args['force_display']   bool    Whether to always show the avatar - ignores the show_avatars option.
 *                                  Default: false.
 * $args['loading']         string  Value for the `loading` attribute.
 *                                  Default: null.
 * $args['extra_attr']      string  HTML attributes to insert in the IMG element. Is not sanitized.
 *                                  Default: empty.
 * </pre>
 *
 * @param   $html   string  Optional. Some HTML content or apply_filters() after `img` tag. Is not sanitized.
 *                          Default: empty.
 *
 * @return  string|false    `img` tag or `parent_wrap`->`img` for the user's avatar. False on failure.
 *
 * @see     User::usp_get_avatar
 *
 * @since   1.0.0
 *
 */
function usp_get_avatar( $user_id, $size = 50, $url = false, $args = [], $html = false ) {
	if ( ! $user_id ) {
		return false;
	}

	return USP()->user( $user_id )->get_avatar( $size, $url, $args, $html );
}

/**
 * Get username by ID.
 *
 * @param   $user_id    int     ID user.
 * @param   $link       string  Return a name with a link to the specified url.
 *                              Default: false.
 * @param   $args       array   Optional. Extra arguments to retrieve username link:
 * <pre>
 * $args['class']   array|string    Additional classes to add to the img element.
 * </pre>
 *
 * @return  string|bool Username or 'false' - if the user for this id does not exist.
 *
 * @see     User::get_username
 *
 * @since   1.0.0
 *
 */
function usp_user_get_username( $user_id = false, $link = false, $args = false ) {
	return USP()->user( $user_id )->get_username( $link, $args );
}

/**
 * Get a link to the user's cover.
 *
 * @param   $user_id            int     ID of the user to get the cover.
 *                                      If not specified, the current office id.
 * @param   $avatar_as_cover    bool    Set to 'true' for return avatar for cover (if the user did not set the cover).
 *                                      Default: false
 *
 * @return  string  URL cover or avatar.
 *
 * @see     User::get_cover_url
 *
 * @since   1.0.0
 *
 */
function usp_user_get_cover_url( $user_id = 0, $avatar_as_cover = false ) {
	$user_id = $user_id ?: usp_office_id();

	if ( ! $user_id ) {
		return '';
	}

	return USP()->user( $user_id )->get_cover_url( $avatar_as_cover );
}

/**
 * Get user profile public fields.
 *
 * @param   $user_id    int ID of user.
 *                          If not specified, the current user id.
 *
 * @return  string      User profile fields.
 *
 * @see     User::profile_fields
 *
 * @since   1.0.0
 *
 */
function usp_user_get_public_profile_fields( $user_id = 0 ) {
	$user_id = $user_id ?: get_current_user_id();

	if ( ! $user_id ) {
		return false;
	}

	return USP()->user( $user_id )->profile_fields()->get_public_fields_values();
}

/**
 * Get user age.
 *
 * @param   $user_id    int ID of user.
 *                          If not specified, the current user id.
 *
 * @return  int|bool        Age user. False - if none.
 *
 * @see     User::get_age
 *
 * @since   1.0.0
 *
 */
function usp_user_get_age( $user_id = 0 ) {
	$user_id = $user_id ?: get_current_user_id();

	if ( ! $user_id ) {
		return false;
	}

	return USP()->user( $user_id )->get_age();
}

/**
 * Get user age box.
 *
 * @param   $user_id    int     ID user.
 *                              If not specified, the current user id.
 * @param   $class      string  Additional class.
 *
 * @return  string  Html box with user age.
 *
 * @see     User::get_age_html
 *
 * @since   1.0.0
 *
 */
function usp_user_get_age_html( $user_id = 0, $class = '' ) {
	$user_id = $user_id ?: get_current_user_id();

	if ( ! $user_id ) {
		return '';
	}

	return USP()->user( $user_id )->get_age_html( $class );
}

/**
 * Get user description.
 *
 * @param   $user_id    int     ID user.
 *                              If not specified, the current user id.
 *
 * @return  string  User description.
 *
 * @see     User::get_description
 *
 * @since   1.0.0
 *
 */
function usp_user_get_description( $user_id = 0 ) {
	$user_id = $user_id ?: get_current_user_id();

	if ( ! $user_id ) {
		return '';
	}

	return USP()->user( $user_id )->get_description();
}

/**
 * Get user description html box.
 *
 * @param   $user_id    int     ID user.
 *                              If not specified, the current user id.
 * @param   $args       array   Optional. Extra arguments:
 * <pre>
 * $args['side']    string  Balloon triangle position.
 *                          Available: left|top
 *                          Default: left
 * $args['class']   string  Additional css class.
 * </pre>
 *
 * @return  string  User description html block.
 *
 * @see     User::get_description_html
 *
 * @since   1.0.0
 *
 */
function usp_user_get_description_html( $user_id = 0, $args = [] ) {
	$user_id = $user_id ?: get_current_user_id();

	if ( ! $user_id ) {
		return '';
	}

	return USP()->user( $user_id )->get_description_html( $args );
}

/**
 * Check if the user has a role.
 *
 * @param   $user_id    int         ID user.
 * @param   $role   string|array    The role that is being checked.
 *
 * @return  bool    True if the user has a role, false if he does not have a role.
 *
 * @see     User::has_role
 *
 * @since   1.0.0
 *
 */
function usp_user_has_role( $user_id, $role ) {
	if ( ! $user_id ) {
		return false;
	}

	return USP()->user( $user_id )->has_role( $role );
}

/**
 * Checks if the user has access to the console.
 *
 * @param   $user_id    int ID user.
 *                          If not specified, the current user is checked.
 *
 * @return  bool    True if the user can access the console, false if he does not have access.
 *
 * @see     User::is_access_console
 *
 * @since   1.0.0
 */
function usp_user_is_access_console( $user_id = 0 ) {
	$user_id = $user_id ?: get_current_user_id();

	if ( ! $user_id ) {
		return false;
	}

	return USP()->user( $user_id )->is_access_console();
}

/**
 * Update user activity.
 *
 * @param   $user_id        int     ID user.
 *                                  If not specified, the current user id.
 * @param   $activity       string  Mysql datetime.
 * @param   $force_update   bool    Forced update ignoring that he is still online.
 *
 * @return  void
 *
 * @see     User::update_activity
 *
 * @since   1.0.0
 *
 */
function usp_user_update_activity( $user_id = 0, $activity = '', $force_update = false ) {
	$user_id = $user_id ?: get_current_user_id();

	if ( ! $user_id ) {
		return;
	}

	if ( ! $force_update && USP()->user( $user_id )->is_online() ) {
		return;
	}

	$activity_timestamp = strtotime( $activity );

	$activity = $activity_timestamp ? gmdate( "Y-m-d H:i:s", $activity_timestamp ) : current_time( 'mysql' );

	USP()->user( $user_id )->update_activity( $activity, $force_update );
}

/**
 * Returns the url to the account specified by the user id.
 * Takes into account the setting of the output of the user profile page.
 *
 * @param   $user_id    int     ID user.
 *                              If not specified, the current user id.
 * @param   $tab        string  ID of the personal account tab.
 * @param   $subtab     string  ID of the personal account subtab.
 *
 * @return string       Url to user.
 *
 * @see     User::get_url
 *
 * @since 1.0.0
 *
 */
function usp_user_get_url( $user_id = 0, $tab = null, $subtab = null ) {
	$user_id = $user_id ?: get_current_user_id();

	if ( ! $user_id ) {
		return '';
	}

	return USP()->user( $user_id )->get_url( $tab, $subtab );
}

/**
 * Get statistics item.
 *
 * @param   $title  string  Title item.
 * @param   $count  int     Counter item.
 * @param   $icon   string  Uspi icon item.
 *                          Default: 'fa-info-circle'
 * @param   $class  string  Additional class item.
 *
 * @return  string  Html statistics item.
 *
 * @since   1.0.0
 *
 */
function usp_user_get_stat_item( $title, $count, $icon = 'fa-info-circle', $class = false ) {
	$data = [
		'title' => $title,
		'count' => $count,
		'icon'  => $icon,
		'class' => $class,
	];

	return usp_get_include_template( 'usp-statistics-item.php', '', $data );
}

/**
 * Check if registration open.
 *
 * @return  bool    True - open registration. False - closed.
 */
function usp_is_register_open() {
	/**
	 * Filter allow you to open or close registration on the site.
	 *
	 * @param bool  Set 1 if you need to allow registration.
	 *              Default: true
	 *
	 * @since       1.0.0
	 *
	 */
	return apply_filters( 'usp_users_can_register', get_site_option( 'users_can_register' ) );
}

add_action( 'delete_user', 'usp_delete_user_action', 10 );
function usp_delete_user_action( $user_id ) {
	global $wpdb;

	// phpcs:ignore
	return $wpdb->query( $wpdb->prepare( "DELETE FROM " . USP_PREF . "users_actions WHERE user_id ='%d'", $user_id ) );
}

add_action( 'delete_user', 'usp_delete_user_avatar', 10 );
function usp_delete_user_avatar( $user_id ) {
	array_map( "unlink", glob( USP_UPLOAD_URL . 'avatars/' . $user_id . '-*.jpg' ) );
}

add_action( 'usp_user_masonry_content', 'usp_user_masonry_content_age', 14 );
function usp_user_masonry_content_age( User $user ) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $user->get_age_html( 'usp-masonry__age' );
}

add_action( 'usp_user_masonry_content', 'usp_user_masonry_content_description', 18 );
function usp_user_masonry_content_description( User $user ) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $user->get_description_html( [ 'side' => 'top' ] );
}

add_action( 'usp_user_masonry_content', 'usp_user_masonry_content_custom_fields', 22 );
function usp_user_masonry_content_custom_fields( User $user ) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<div class="usp-masonry__fields">' . $user->profile_fields()->get_public_fields_values() . '</div>';
}

add_action( 'usp_user_full_meta', 'usp_user_full_meta_age', 20 );
function usp_user_full_meta_age( User $user ) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $user->get_age_html( 'usp-user__age' );
}

add_action( 'usp_user_stats', 'usp_user_stats_registration_date', 12, 2 );
function usp_user_stats_registration_date( User $user, $custom_data = [] ) {
	if ( ! in_array( 'user_registered', $custom_data, true ) ) {
		return;
	}

	$title = esc_html__( 'Registration date', 'userspace' );
	$count = sanitize_key( mysql2date( 'd-m-Y', $user->user_registered ) );
	$icon  = 'fa-calendar-check';
	$class = 'usp-meta__registration';

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo usp_user_get_stat_item( $title, $count, $icon, $class );
}

add_action( 'usp_user_stats', 'usp_user_after_registration', 18, 2 );
function usp_user_after_registration( User $user ) {
	$user_registered = mysql2date( 'd-m-Y', $user->user_registered );
	$current_day     = get_date_from_gmt( gmdate( 'Y-m-d H:i:s' ), 'Y-m-d' );

	$d_register = date_create( $user_registered );
	$d_current  = date_create( $current_day );
	$interval   = date_diff( $d_register, $d_current );

	/* $interval returns:
	  DateInterval Object
	  (
	  [y] => 5
	  [m] => 3
	  [d] => 12
	  [h] => 0
	  [i] => 0
	  [s] => 0
	  [weekday] => 0
	  [weekday_behavior] => 0
	  [first_last_day_of] => 0
	  [invert] => 0
	  [days] => 1930
	  [special_type] => 0
	  [special_amount] => 0
	  [have_weekday_relative] => 0
	  [have_special_relative] => 0
	  ) */

	$title = esc_html__( 'Days on the site', 'userspace' );
	$count = absint( $interval->days );
	$icon  = 'fa-coffee';
	$class = 'usp-meta__days';

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo usp_user_get_stat_item( $title, $count, $icon, $class );
}

add_action( 'usp_user_stats', 'usp_user_stats_comments', 28, 2 );
function usp_user_stats_comments( User $user, $custom_data = [] ) {
	if ( ! in_array( 'comments', $custom_data, true ) || ! is_numeric( $user->comments ) ) {
		return;
	}

	$title = esc_html__( 'Comments', 'userspace' );
	$count = absint( $user->comments );
	$icon  = 'fa-comment';
	$class = 'usp-meta__comments';

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo usp_user_get_stat_item( $title, $count, $icon, $class );
}

add_action( 'usp_user_stats', 'usp_user_stats_posts', 34, 2 );
function usp_user_stats_posts( User $user, $custom_data = [] ) {
	if ( ! in_array( 'posts', $custom_data, true ) || ! is_numeric( $user->posts ) ) {
		return;
	}

	$title = esc_html__( 'Publications', 'userspace' );
	$count = absint( $user->posts );
	$icon  = 'fa-file';
	$class = 'usp-meta__posts';

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo usp_user_get_stat_item( $title, $count, $icon, $class );
}

// replace the link of the comment author with the link of his personal account
add_filter( 'get_comment_author_url', 'usp_get_link_author_comment', 10, 3 );
function usp_get_link_author_comment( $url, $id, $comment ) {
	return ! $comment->user_id ? $url : usp_user_get_url( $comment->user_id );
}

// fixes the link to the user account page
add_filter( 'author_link', 'usp_author_link', 999, 2 );
function usp_author_link( $link, $author_id ) {
	return usp_user_get_url( $author_id );
}

add_action( 'usp_user_fields_after', 'usp_user_notice_add_some_data', 100, 3 );
function usp_user_notice_add_some_data( $user, $custom_data, $template ) {
	if ( 'full' !== $template ) {
		return;
	}

	// owner account
	if ( USP()->office()->is_owner( get_current_user_id() ) ) {
		$text = false;

		if ( empty( $user->metadata['usp_avatar'] ) ) {
			$text .= '<div class="usp-must usp-must__ava">' . usp_get_button( [
					'type'    => 'clear',
					'label'   => esc_html__( 'Upload avatar', 'userspace' ),
					'onclick' => 'usp_focus_upload_buttons(this,"ava");return false;',
				] ) . '</div>';
		}

		if ( ! $user->is_cover() ) {
			$text .= '<div class="usp-must usp-must__cover">' . usp_get_button( [
					'type'    => 'clear',
					'label'   => esc_html__( 'Upload cover', 'userspace' ),
					'onclick' => 'usp_focus_upload_buttons(this,"cover");return false;',
				] ) . '</div>';
		}

		if ( isset( $user->profile_fields()->fields['description'] ) && empty( $user->metadata['description'] ) ) {
			$text .= '<div class="usp-must usp-must__about">' . usp_get_button( [
					'type'    => 'clear',
					'class'   => 'usp-must-bttn usp-must-bttn__about',
					'label'   => esc_html__( 'Write a few words about yourself', 'userspace' ),
					'onclick' => 'usp_load_tab("profile", "edit", this);return false;',
				] ) . '</div>';
		}

		if ( empty( $user->profile_fields()->get_public_fields_values() ) ) {
			$text .= '<div class="usp-must usp-must__fields">' . usp_get_button( [
					'type'    => 'clear',
					'class'   => 'usp-must-bttn usp-must-bttn__fields',
					'label'   => esc_html__( 'Fill in the fields in your profile', 'userspace' ),
					'onclick' => 'usp_load_tab("profile", "edit", this);return false;',
				] ) . '</div>';
		}

		if ( ! $text ) {
			return;
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo usp_get_notice( [
			'type'        => 'simple',
			'icon'        => 'fa-info-circle',
			'title'       => esc_html__( 'You can:', 'userspace' ),
			'text'        => $text,
			'text_center' => false,
			'cookie'      => 'usp_profile_must',
		] );
	} else {
		if (
			isset( $user->profile_fields()->fields['description'] )
			&& empty( $user->metadata['description'] )
			&& empty( $user->profile_fields()->get_public_fields_values() )
		) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo usp_get_notice( [
				'type' => 'simple',
				'text' => esc_html__( 'The user has not told anything about himself yet.', 'userspace' ),
			] );
		}
	}
}

/**
 * Return menu object for user profile
 *
 * @param User $user
 *
 * @return USP_Dropdown_Menu
 */
function usp_get_user_profile_menu( User $user ) {

	$menu = new USP_Dropdown_Menu( 'usp_user_profile_menu', [
		'custom_data'       => [
			'user' => $user
		],
		'open_button'       => [
			'icon' => 'fa-angle-down',
			'size' => 'small'
		],
		'open_button_style' => 'transparent',
	] );

	if ( is_user_logged_in() ) {

		if ( get_current_user_id() != $user->ID ) {

			$user_block = USP()->user( get_current_user_id() )->is_blocked( $user->ID );

			$title = ( $user_block ) ? __( 'Unblock', 'userspace' ) : __( 'Block', 'userspace' );

			$menu->add_button( [
				'id'      => 'blacklist',
				'label'   => $title,
				'icon'    => 'fa-user',
				'onclick' => 'usp_manage_user_black_list(this, ' . $user->ID . ', "' . __( 'Are you sure?', 'userspace' ) . '");return false;'
			] );

		}
	}

	return $menu;

}
