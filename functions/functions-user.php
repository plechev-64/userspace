<?php

/**
 * Retrieve the avatar `<img>` tag for a user
 * wraps it, if necessary, in the parent tag <a> or <div>
 *
 * @param int $user_id id of an user
 * @param int $size Optional. Height and width of the avatar image file in pixels. Default 50.
 * @param string $url Optional. URL for the parent wrapper. Or # if use $args => $parent_onclick
 * @param array $args {
 *                                    Optional. Extra arguments to retrieve the avatar.
 *
 * @type string $parent_wrap The img tag to wrap the parent tag. <a> or <div>. Default <a>.
 * @type string $parent_id id of the parent tag
 * @type string $parent_class class of the parent tag
 * @type string $parent_title title of the parent tag
 * @type string $parent_onclick onclick of the parent tag (set $url as #)
 *
 * @type string $alt Alternative text to use in img tag. Default empty.
 *
 *
 * @type int $height Display height of the avatar in pixels. Defaults to $size.
 * @type int $width Display width of the avatar in pixels. Defaults to $size.
 * @type bool $force_default Whether to always show the default image, never the Gravatar. Default false.
 * @type string $rating What rating to display avatars up to. Accepts 'G', 'PG', 'R', 'X', and are
 *                                       judged in that order. Default is the value of the 'avatar_rating' option.
 * @type string $scheme URL scheme to use. See set_url_scheme() for accepted values.
 *                                       Default null.
 * @type array|string $class Array or string of additional classes to add to the img element.
 *                                       Default null.
 * @type bool $force_display Whether to always show the avatar - ignores the show_avatars option.
 *                                       Default false.
 * @type string $loading Value for the `loading` attribute.
 *                                       Default null.
 * @type string $extra_attr HTML attributes to insert in the IMG element. Is not sanitized. Default empty.
 * }
 *
 * @param string $html Optional. Some HTML content or apply_filters() after <img> tag. Is not sanitized. Default empty.
 *
 * @return string|false `<img>` tag or <parent_wrap><img></parent_wrap> for the user's avatar. False on failure
 * @since 1.0
 *
 */
function usp_get_avatar( $user_id, $size = 50, $url = false, $args = [], $html = false ) {
	if ( ! $user_id ) {
		return false;
	}

	return USP()->user( $user_id )->get_avatar( $size, $url, $args, $html );

}

/**
 * Get username by id
 *
 * @param int $user_id id user.
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
function usp_user_get_username( $user_id = false, $link = false, $args = false ) {

	return USP()->user( $user_id )->get_username( $link, $args );

}

/**
 * Get url to user cover
 *
 * @param int $user_id id of the user to get the cover.
 * @param bool $avatar_as_cover set to 'true' for return avatar for cover (if the user did not set the cover).
 *                               Default: false
 *
 * @return string url cover or avatar.
 * @since 1.0
 *
 */
function usp_user_get_cover( $user_id = 0, $avatar_as_cover = false ) {

	$user_id = $user_id ?: usp_office_id();

	if ( ! $user_id ) {
		return '';
	}

	return USP()->user( $user_id )->get_cover_url( $avatar_as_cover );
}

/**
 * Get user age
 *
 * @param int $user_id id user.
 *
 * @return int|bool         age user. false - if none
 * @since 1.0
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
 * Get user age box
 *
 * @param int $user_id id user.
 * @param string $class additional class.
 *
 * @return string   html box with user age
 * @since 1.0
 *
 */
function usp_user_get_age_html( $user_id = 0, $class = '' ) {

	$user_id = $user_id ?: get_current_user_id();

	if ( ! $user_id ) {
		return '';
	}

	return USP()->user( $user_id )->get_age_html( $class );
}

function usp_user_rayting() {
	if ( ! in_array( 'userspace-rating-system/userspace-rating-system.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		return;
	}

	global $usp_user, $usp_users_set;

	if ( false !== array_search( 'rating_total', $usp_users_set->data ) || isset( $usp_user->rating_total ) ) {
		if ( ! isset( $usp_user->rating_total ) ) {
			$usp_user->rating_total = 0;
		}

		echo uspr_rating_block( array( 'value' => $usp_user->rating_total ) );
	}
}

function usp_get_user_custom_fields() {
	global $usp_user, $usp_users_set;

	if ( false !== array_search( 'profile_fields', $usp_users_set->data ) || isset( $usp_user->profile_fields ) ) {
		if ( ! isset( $usp_user->profile_fields ) ) {
			return;
		}

		$out = '';
		foreach ( $usp_user->profile_fields as $field_id => $field ) {
			$out .= USP_Field::setup( $field )->get_field_value( 'title' );
		}

		return $out;
	}
}

add_action( 'usp_user_stats', 'usp_user_stats_comments', 22, 2 );
function usp_user_stats_comments( USP_User $user, $custom_data = [] ) {

	if ( ! in_array( 'comments', $custom_data ) || ! is_numeric( $user->comments ) ) {
		return;
	}

	$title = __( 'Comments', 'userspace' ) . ':';
	$count = $user->comments;
	$icon  = 'fa-comment';
	$class = 'usp-meta__comm';

	echo usp_user_get_stat_item( $title, $count, $icon, $class );
}

add_action( 'usp_user_stats', 'usp_user_stats_posts', 21, 2 );
function usp_user_stats_posts( USP_User $user, $custom_data = [] ) {

	if ( ! in_array( 'posts', $custom_data ) || ! is_numeric( $user->posts ) ) {
		return;
	}

	$title = __( 'Publics', 'userspace' ) . ':';
	$count = $user->posts;
	$icon  = 'fa-file';
	$class = 'usp-meta__post';

	echo usp_user_get_stat_item( $title, $count, $icon, $class );
}

add_action( 'usp_user_stats', 'usp_user_stats_register', 23, 2 );
function usp_user_stats_register( USP_User $user, $custom_data = [] ) {

	if ( ! in_array( 'user_registered', $custom_data ) ) {
		return;
	}

	$title = __( 'Registration', 'userspace' ) . ':';
	$count = mysql2date( 'd-m-Y', $user->user_registered );
	$icon  = 'fa-calendar-check';
	$class = 'usp-meta__reg';

	echo usp_user_get_stat_item( $title, $count, $icon, $class );
}

/**
 * Get statistics item
 *
 * @param string $title title item.
 * @param int $count counter item.
 * @param string $icon uspi icon item.
 * @param string $class additional class item.
 *
 * @return string       html item
 * @since 1.0
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
 * @param int $user_id
 * @param array $attr $attr['side'] left|top (default: left)
 *                           $attr['class'] additional css class
 *
 * @return string user description html block
 */
function usp_user_get_description( $user_id = 0, $attr = [] ) {

	$user_id = $user_id ?: get_current_user_id();

	if ( ! $user_id ) {
		return '';
	}

	return USP()->user( $user_id )->get_description_html( $attr );
}

add_filter( 'usp_users_search_form', 'usp_default_search_form' );
function usp_default_search_form( $content ) {

	$search_text  = ( ( isset( $_GET['search_text'] ) ) ) ? $_GET['search_text'] : '';
	$search_field = ( isset( $_GET['search_field'] ) ) ? sanitize_key( $_GET['search_field'] ) : 'display_name';

	$fields  = array(
		array(
			'type'    => 'text',
			'slug'    => 'search_text',
			'title'   => __( 'Search users', 'userspace' ),
			'default' => $search_text,
		),
		array(
			'type'    => 'radio',
			'slug'    => 'search_field',
			'values'  => array(
				'display_name' => __( 'by name', 'userspace' ),
				'user_login'   => __( 'by login', 'userspace' ),
				'usp_birthday' => __( 'by birthday', 'userspace' ),
				'usp_sex'      => __( 'by sex', 'userspace' )
			),
			'default' => $search_field,
		),
		array(
			'type'  => 'hidden',
			'slug'  => 'default-search',
			'value' => 1
		)
	);
	$content .= usp_get_form( [
		'class'  => 'usp-users__search',
		'method' => 'get',
		'submit' => __( 'Search', 'userspace' ),
		'fields' => $fields
	] );

	return $content;
}

/**
 * @param int $user_id
 * @param string|array $role
 *
 * @return bool
 */
function usp_user_has_role( $user_id, $role ) {

	if ( ! $user_id ) {
		return false;
	}

	return USP()->user( $user_id )->has_role( $role );

}

/**
 * @param int $user_id
 * @param string $activity myslq datetime
 * @param bool $force_update
 *
 * @return void
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

	$activity = $activity_timestamp ? date( "Y-m-d H:i:s", $activity_timestamp ) : current_time( 'mysql' );

	USP()->user( $user_id )->update_activity( $activity, $force_update );
}

// replace the link of the comment author with the link of his personal account
add_filter( 'get_comment_author_url', 'usp_get_link_author_comment', 10 );
function usp_get_link_author_comment( $url ) {
	global $comment;
	if ( ! isset( $comment ) || $comment->user_id == 0 ) {
		return $url;
	}

	return usp_user_get_url( $comment->user_id );
}

function usp_is_register_open() {
	return apply_filters( 'usp_users_can_register', get_site_option( 'users_can_register' ) );
}

add_filter( 'author_link', 'usp_author_link', 999, 2 );
function usp_author_link( $link, $author_id ) {
	return usp_user_get_url( $author_id );
}

/**
 * Returns the url to the account specified by the user id
 * Takes into account the setting of the output of the user profile page
 *
 * @param int $user_id id user.
 *
 * @return string   url to user.
 * @since 1.0
 *
 */
function usp_user_get_url( $user_id = 0 ) {

	$user_id = $user_id ?: get_current_user_id();

	if ( ! $user_id ) {
		return '';
	}

	return USP()->user( $user_id )->get_url();
}

/**
 * @param int $user_id
 *
 * @return bool can user access to console
 */
function usp_user_is_access_console( $user_id = 0 ) {

	$user_id = $user_id ?: get_current_user_id();

	if ( ! $user_id ) {
		return false;
	}

	return USP()->user( $user_id )->is_access_console();
}

add_action( 'delete_user', 'usp_delete_user_action', 10 );
function usp_delete_user_action( $user_id ) {
	global $wpdb;

	return $wpdb->query( $wpdb->prepare( "DELETE FROM " . USP_PREF . "users_actions WHERE user_id ='%d'", $user_id ) );
}

add_action( 'delete_user', 'usp_delete_user_avatar', 10 );
function usp_delete_user_avatar( $user_id ) {
	array_map( "unlink", glob( USP_UPLOAD_URL . 'avatars/' . $user_id . '-*.jpg' ) );
}

/**
 * Get user custom fields
 *
 * @param int $user_id id user.
 * @param array $args $args['class'] add some class.
 *
 * @return string       user custom fields
 * @since 1.0
 *
 */
function usp_show_user_custom_fields( $user_id, $args = false ) {

	$public_fields = USP()->user( $user_id )->profile_fields()->get_public_fields();

	if ( ! $public_fields ) {
		return;
	}

	$content = '';

	foreach ( $public_fields as $field ) {

		/**
		 * @var USP_Field_Abstract $field
		 */

		$field = apply_filters( 'usp_profile_pre_display_custom_field', $field );

		if ( ! $field ) {
			continue;
		}

		$field->value = USP()->user( $user_id )->{$field->slug};

		$content .= $field->get_field_value( true );
	}

	if ( ! $content ) {
		return;
	}

	$class = ( $args['class'] ) ? ' ' . $args['class'] : '';

	return '<div class="usp-user-fields ' . $class . ' usps usps__column">' . $content . '</div>';
}

add_action( 'usp_masonry_content', 'usp_masonry_age', 14 );
function usp_masonry_age( USP_User $user ) {
	echo $user->get_age_html( 'usp-masonry__age' );
}

add_action( 'usp_masonry_content', 'usp_masonry_description', 18 );
function usp_masonry_description( USP_User $user ) {
	echo $user->get_description_html( [ 'side' => 'top' ] );
}

add_action( 'usp_masonry_content', 'usp_masonry_custom_fields', 22 );
function usp_masonry_custom_fields( USP_User $user ) {
	echo '<div class="usp-masonry__fields">' . $user->profile_fields()->get_public_fields_values() . '</div>';
}

/**
 * gets a block of the number of comments of the specified user
 *
 * @param int $user_id id user
 *
 * @return string   html block
 * @since 1.0
 *
 */
function usp_user_count_comments( $user_id ) {
	global $wpdb;
	$comm_count = $wpdb->get_var( "SELECT COUNT(comment_ID) FROM " . $wpdb->comments . " WHERE user_id = " . $user_id . " AND comment_approved = 1" );

	$title = __( 'Comments', 'userspace' ) . ':';
	$count = $comm_count;
	$icon  = 'fa-comment';
	$class = 'usp-meta__comm';

	echo usp_user_get_stat_item( $title, $count, $icon, $class );
}

/**
 * gets a block of the number of publications of the specified user
 *
 * @param int $user_id id user
 *
 * @return string   html block
 * @since 1.0
 *
 */
function usp_user_count_publications( $user_id ) {
	global $wpdb;

	$exclude_post_by_type = "'page','nav_menu_item','customize_changeset','oembed_cache','custom_css','wp_block'";

	$exclude_posts_type = apply_filters( 'usp_user_count_publications_exclude_post_types', $exclude_post_by_type );

	$post_count = $wpdb->get_var( ""
	                              . "SELECT COUNT(ID) "
	                              . "FROM " . $wpdb->posts . " "
	                              . "WHERE post_author = " . $user_id . " "
	                              . "AND post_status IN ('publish', 'private') "
	                              . "AND post_type NOT IN(" . $exclude_posts_type . ") " );

	$title = __( 'Publics', 'userspace' ) . ':';
	$count = $post_count;
	$icon  = 'fa-file';
	$class = 'usp-meta__post';

	echo usp_user_get_stat_item( $title, $count, $icon, $class );
}

/**
 * gets a block with the registration date of the specified user
 *
 * @param int $user_id id user
 *
 * @return string   html block
 * @since 1.0
 *
 */
function usp_user_get_date_registered( $user_id ) {
	if ( ! $user_id ) {
		return;
	}

	$register_date = get_userdata( $user_id );

	$title = __( 'Registration', 'userspace' ) . ':';
	$count = mysql2date( 'd-m-Y', $register_date->user_registered );
	$icon  = 'fa-calendar-check';
	$class = 'usp-meta__reg';

	echo usp_user_get_stat_item( $title, $count, $icon, $class );
}
