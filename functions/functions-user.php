<?php

/**
 * Retrieve the avatar `<img>` tag for a user
 * wraps it, if necessary, in the parent tag <a> or <div>
 *
 * @since 1.0
 *
 * @param mixed  $id_or_email   The Gravatar to retrieve. Accepts a user_id, gravatar md5 hash,
 *                              user email, WP_User object, WP_Post object, or WP_Comment object.
 * @param int    $size          Optional. Height and width of the avatar image file in pixels. Default 50.
 * @param string $url           Optional. URL for the parent wrapper. Or # if use $args => $parent_onclick
 * @param array  $args {
 *     Optional. Extra arguments to retrieve the avatar.
 *
 *     @type string       $parent_wrap      The img tag to wrap the parent tag. <a> or <div>. Default <a>.
 *     @type string       $parent_id        id of the parent tag
 *     @type string       $parent_class     class of the parent tag
 *     @type string       $parent_title     title of the parent tag
 *     @type string       $parent_onclick   onclick of the parent tag (set $url as #)
 *
 *     @type string       $alt              Alternative text to use in img tag. Default empty.
 *
 *
 *     @type int          $height        Display height of the avatar in pixels. Defaults to $size.
 *     @type int          $width         Display width of the avatar in pixels. Defaults to $size.
 *     @type bool         $force_default Whether to always show the default image, never the Gravatar. Default false.
 *     @type string       $rating        What rating to display avatars up to. Accepts 'G', 'PG', 'R', 'X', and are
 *                                       judged in that order. Default is the value of the 'avatar_rating' option.
 *     @type string       $scheme        URL scheme to use. See set_url_scheme() for accepted values.
 *                                       Default null.
 *     @type array|string $class         Array or string of additional classes to add to the img element.
 *                                       Default null.
 *     @type bool         $force_display Whether to always show the avatar - ignores the show_avatars option.
 *                                       Default false.
 *     @type string       $loading       Value for the `loading` attribute.
 *                                       Default null.
 *     @type string       $extra_attr    HTML attributes to insert in the IMG element. Is not sanitized. Default empty.
 * }
 * @param string    $html  Optional. Some HTML content or apply_filters() after <img> tag. Is not sanitized. Default empty.
 *
 * @return string|false `<img>` tag or <parent_wrap><img></parent_wrap> for the user's avatar. False on failure
 */
function usp_get_avatar( $id_or_email, $size = 50, $url = false, $args = [], $html = false ) {
    if ( ! $id_or_email )
        return false;

    $alt = (isset( $args['parent_alt'] )) ? $args['parent_alt'] : '';

    // class for avatar userspace and class css reset for <img> tag
    ( isset( $args['class'] ) ) ? $args['class'] .= ' usp-ava-img usps__img-reset' : $args['class'] = 'usp-ava-img usps__img-reset';

    global $user_ID;

    // class for current user (realtime reload on avatar upload)
    if ( is_user_logged_in() && is_numeric( $id_or_email ) && ( int ) $id_or_email == $user_ID ) {
        $args['class'] .= ' usp-profile-ava';
    }

    if ( $url || isset( $args['parent_wrap'] ) && $args['parent_wrap'] == 'div' ) {

        $wrap_tag = ( ! isset( $args['parent_wrap'] ) || $args['parent_wrap'] == 'a') ? 'a' : 'div';
        $id       = (isset( $args['parent_id'] )) ? 'id="' . esc_attr( $args['parent_id'] ) . '"' : '';
        $class    = (isset( $args['parent_class'] )) ? 'class="' . esc_attr( $args['parent_class'] ) . '"' : '';
        $title    = (isset( $args['parent_title'] )) ? 'title="' . esc_attr( $args['parent_title'] ) . '"' : '';
        $onclick  = (isset( $args['parent_onclick'] )) ? 'onclick="' . esc_attr( $args['parent_onclick'] ) . '"' : '';
        $href     = ($url) ? 'href="' . esc_url( $url ) . '"' : '';
        $nofollow = ($wrap_tag == 'a') ? 'rel="nofollow"' : '';

        $parent_tag = sprintf(
            "<{$wrap_tag} %s %s %s %s %s %s>", $id, $class, $href, $title, $onclick, $nofollow,
        );

        $parent_tag .= get_avatar( $id_or_email, $size, false, $alt, $args );

        // some html or apply_filters
        if ( isset( $html ) ) {
            $parent_tag .= $html;
        }

        $parent_tag .= "</{$wrap_tag}>";

        return $parent_tag;
    }

    return get_avatar( $id_or_email, $size, false, $alt, $args );
}

/**
 * Get username by id
 *
 * @since 1.0
 *
 * @param int       $user_id    id user.
 * @param string    $link       Return a name with a link to the specified url
 *                              Default 'false'.
 * @param array  $args {
 *     Optional. Extra arguments to retrieve username link.
 *
 *     @type array|string   $class  Array or string of additional classes to add to the img element.
 * }
 *
 * @return string|bool  username or 'false' - if the user for this id does not exist
 */
function usp_get_username( $user_id = false, $link = false, $args = false ) {
    global $usp_user;

    if ( isset( $usp_user ) && ! ($user_id) ) {
        $name = $usp_user->display_name;
    } else {
        if ( ! $user_id )
            return;

        $user_data = get_userdata( $user_id );

        if ( false === $user_data )
            return false;

        $name = ($user_data->display_name) ? $user_data->display_name : $user_data->user_login;
    }

    if ( false === $link ) {
        return $name;
    }

    $class = [ 'usp_userlink' ];

    if ( $args['class'] ) {
        if ( is_array( $args['class'] ) ) {
            $class = array_merge( $class, $args['class'] );
        } else {
            $class[] = $args['class'];
        }
    }

    return '<a class="' . esc_attr( implode( ' ', $class ) ) . '" href="' . $link . '" rel="nofollow">' . $name . '</a>';
}

/**
 * Get url to user cover
 *
 * @since 1.0
 *
 * @param int   $user_id        id of the user to get the avatar.
 * @param bool  $avatar_cover   set to 'true' for return avatar for cover (if the user did not set the cover).
 *                              Default: false
 *
 * @return string url cover or avatar.
 */
function usp_get_user_cover( $user_id = false, $avatar_cover = false ) {
    if ( ! $user_id ) {
        $user_id = usp_office_id();
    }

    $cover = get_user_meta( $user_id, 'usp_cover', 1 );

    if ( ! $cover )
        $cover = usp_get_option( 'usp_default_cover', 0 );

    if ( ! $cover ) {
        return usp_get_default_cover( $avatar_cover, $user_id );
    } else {
        return wp_get_attachment_image_url( $cover, 'large' );
    }
}

/**
 * Get user age
 *
 * @since 1.0
 *
 * @param int   $user_id    id user.
 *
 * @return int|bool         age user. false - if none
 */
function usp_get_age_number( $user_id = false ) {
    if ( ! $user_id ) {
        global $user_LK;

        if ( ! $user_LK )
            return;

        $user_id = $user_LK;
    }

    $bip_birthday = get_user_meta( $user_id, 'usp_birthday', true );

    // there is no data
    if ( ! $bip_birthday )
        return false;

    return date_diff( date_create( $bip_birthday ), date_create( 'today' ) )->y;
}

/**
 * Get user age box
 *
 * @since 1.0
 *
 * @param int       $user_id    id user.
 * @param string    $class      additional class.
 *
 * @return string   html box with user age
 */
function usp_get_user_age( $user_id, $class = false ) {
    $age = usp_get_age_number( $user_id );

    if ( $age ) {
        return '<div class="usp-age ' . $class . '">' . sprintf( _n( '%s year', '%s years', $age, 'userspace' ), $age ) . '</div>';
    }

    return false;
}

function usp_user_rayting() {
    if ( ! in_array( 'userspace-rating-system/userspace-rating-system.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) )
        return;

    global $usp_user, $usp_users_set;

    if ( false !== array_search( 'rating_total', $usp_users_set->data ) || isset( $usp_user->rating_total ) ) {
        if ( ! isset( $usp_user->rating_total ) )
            $usp_user->rating_total = 0;

        echo uspr_rating_block( array( 'value' => $usp_user->rating_total ) );
    }
}

function usp_get_user_custom_fields() {
    global $usp_user, $usp_users_set;

    if ( false !== array_search( 'profile_fields', $usp_users_set->data ) || isset( $usp_user->profile_fields ) ) {
        if ( ! isset( $usp_user->profile_fields ) )
            return;

        $out = '';
        foreach ( $usp_user->profile_fields as $field_id => $field ) {
            $out .= USP_Field::setup( $field )->get_field_value( 'title' );
        }

        return $out;
    }
}

add_action( 'usp_user_stats', 'usp_user_comments', 22 );
function usp_user_comments() {
    global $usp_user, $usp_users_set;

    if ( false !== array_search( 'comments_count', $usp_users_set->data ) || isset( $usp_user->comments_count ) ) {
        if ( ! isset( $usp_user->comments_count ) )
            $usp_user->comments_count = 0;

        $title = __( 'Comments', 'userspace' ) . ':';
        $count = $usp_user->comments_count;
        $icon  = 'fa-comment';
        $class = 'usp-meta__comm';

        echo usp_user_get_stat_item( $title, $count, $icon, $class );
    }
}

add_action( 'usp_user_stats', 'usp_user_posts', 21 );
function usp_user_posts() {
    global $usp_user, $usp_users_set;

    if ( false !== array_search( 'posts_count', $usp_users_set->data ) || isset( $usp_user->posts_count ) ) {
        if ( ! isset( $usp_user->posts_count ) )
            $usp_user->posts_count = 0;

        $title = __( 'Publics', 'userspace' ) . ':';
        $count = $usp_user->posts_count;
        $icon  = 'fa-file';
        $class = 'usp-meta__post';

        echo usp_user_get_stat_item( $title, $count, $icon, $class );
    }
}

add_action( 'usp_user_stats', 'usp_user_register', 23 );
function usp_user_register() {
    global $usp_user, $usp_users_set;

    if ( false !== array_search( 'user_registered', $usp_users_set->data ) || isset( $usp_user->user_registered ) ) {
        if ( ! isset( $usp_user->user_registered ) )
            return;

        $title = __( 'Registration', 'userspace' ) . ':';
        $count = mysql2date( 'd-m-Y', $usp_user->user_registered );
        $icon  = 'fa-calendar-check';
        $class = 'usp-meta__reg';

        echo usp_user_get_stat_item( $title, $count, $icon, $class );
    }
}

/**
 * Get statistics item
 *
 * @since 1.0
 *
 * @param string    $title      title item.
 * @param int       $count      counter item.
 * @param string    $icon       uspi icon item.
 * @param string    $class      additional class item.
 *
 * @return string       html item
 */
function usp_user_get_stat_item( $title, $count, $icon = 'fa-info-circle', $class = false ) {
    $data = [
        'title' => $title,
        'count' => $count,
        'icon'  => $icon,
        'class' => $class,
    ];

    return usp_get_include_template( 'statistics-item.php', '', $data );
}

function usp_get_user_description( $user_id = false, $attr = false ) {
    global $usp_user;

    if ( ! isset( $usp_user->description ) )
        return;

    $args = wp_parse_args( $attr, [ 'text' => $usp_user->description, 'class' => 'usp-user__description' ] );

    if ( isset( $usp_user->description ) && $usp_user->description ) {
        return usp_get_quote_box( $usp_user->ID, $args );
    }
}

add_filter( 'usp_users_search_form', 'usp_default_search_form' );
function usp_default_search_form( $content ) {
    global $user_LK, $usp_tab;

    $search_text  = ((isset( $_GET['search_text'] ))) ? $_GET['search_text'] : '';
    $search_field = (isset( $_GET['search_field'] )) ? sanitize_key( $_GET['search_field'] ) : 'display_name';

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

//    if ( $user_LK && $usp_tab ) {
//
//        $get = usp_get_option( 'usp_user_account_slug', 'user' );
//
//        $content .= '<input type="hidden" name="' . $get . '" value="' . $user_LK . '">';
//        $content .= '<input type="hidden" name="tab" value="' . $usp_tab->id . '">';
//    }

    return $content;
}

function usp_is_user_role( $user, $roles ) {
    if ( ! $user )
        $user = wp_get_current_user();
    if ( is_numeric( $user ) )
        $user = get_userdata( $user );

    if ( empty( $user->ID ) )
        return false;

    foreach ( ( array ) $roles as $role )
        if ( isset( $user->caps[$role] ) || in_array( $role, $user->roles ) )
            return true;

    return false;
}

function usp_human_time_diff( $time_action ) {
    $unix_current_time = strtotime( current_time( 'mysql' ) );
    $unix_time_action  = strtotime( $time_action );
    return human_time_diff( $unix_time_action, $unix_current_time );
}

function usp_update_timeaction_user() {
    global $user_ID, $wpdb;

    if ( ! $user_ID )
        return false;

    $usp_current_action = usp_get_time_user_action( $user_ID );

    $last_action = usp_get_useraction( $usp_current_action );

    if ( $last_action ) {

        $time = current_time( 'mysql' );

        $res = $wpdb->update(
            USP_PREF . 'users_actions', array( 'date_action' => $time ), array( 'user_id' => $user_ID )
        );

        if ( ! isset( $res ) || $res == 0 ) {
            $act_user = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(date_action) FROM " . USP_PREF . "users_actions WHERE user_id ='%d'", $user_ID ) );
            if ( $act_user == 0 ) {
                $wpdb->insert(
                    USP_PREF . 'users_actions', array( 'user_id'     => $user_ID,
                    'date_action' => $time )
                );
            }
            if ( $act_user > 1 ) {
                usp_delete_user_action( $user_ID );
            }
        }
    }

    do_action( 'usp_update_timeaction_user' );
}

function usp_get_user_action( $type = 1 ) {
    global $usp_user;

    $action = (isset( $usp_user->date_action )) ? $usp_user->date_action : $usp_user->user_registered;

    switch ( $type ) {
        case 1: $last_action = usp_get_useraction( $action );
            if ( ! $last_action ) {
                return '<i class="uspi fa-circle usp-status-user usp-online"></i>';
            } else {
                return '<i class="uspi fa-circle usp-status-user usp-offline" title="' . __( 'offline', 'userspace' ) . ' ' . $last_action . '"></i>';
            }
            break;
        case 2: return usp_get_miniaction( $action );
    }
}

function usp_user_action( $type = 1 ) {
    global $usp_user;

    $action = (isset( $usp_user->date_action )) ? $usp_user->date_action : $usp_user->user_registered;

    switch ( $type ) {
        case 1: $last_action = usp_get_useraction( $action );
            if ( ! $last_action )
                echo '<i class="uspi fa-circle usp-status-user usp-online"></i>';
            else
                echo '<i class="uspi fa-circle usp-status-user usp-offline" title="' . __( 'offline', 'userspace' ) . ' ' . $last_action . '"></i>';
            break;
        case 2: echo usp_get_miniaction( $action );
            break;
    }
}

function usp_get_useraction( $user_action = false ) {
    global $usp_Office_Action;

    if ( ! $user_action )
        $user_action = $usp_Office_Action;

    $unix_time_user = strtotime( $user_action );

    if ( ! $unix_time_user || $user_action == '0000-00-00 00:00:00' )
        return __( 'long ago', 'userspace' );

    $timeout = usp_get_option( 'usp_user_timeout', 10 ) * 60;

    $unix_time_action = strtotime( current_time( 'mysql' ) );

    if ( $unix_time_action > $unix_time_user + $timeout ) {
        return human_time_diff( $unix_time_user, $unix_time_action );
    } else {
        return false;
    }
}

function usp_get_useraction_html( $user_id, $type = 1 ) {

    $action = usp_get_time_user_action( $user_id );

    switch ( $type ) {
        case 1:

            $last_action = usp_get_useraction( $action );

            if ( ! $last_action )
                return '<i class="uspi fa-circle usp-status-user usp-online"></i>';
            else
                return '<i class="uspi fa-circle usp-status-user usp-offline" title="' . __( 'offline', 'userspace' ) . ' ' . $last_action . '"></i>';

            break;
        case 2:

            return usp_get_miniaction( $action );

            break;
    }
}

function usp_get_time_user_action( $user_id ) {

    $cachekey = json_encode( array( 'usp_get_time_user_action', ( int ) $user_id ) );
    $cache    = wp_cache_get( $cachekey );
    if ( $cache )
        return $cache;

    $action = RQ::tbl( new USP_User_Action() )->select( [ 'date_action' ] )->where( [ 'user_id' => $user_id ] )->get_var();

    if ( ! $action ) {
        $action = '0000-00-00 00:00:00';
    }

    wp_cache_add( $cachekey, $action, 'default', usp_get_option( 'usp_user_timeout', 10 ) * 60 );

    return $action;
}

function usp_get_miniaction( $action ) {
    global $usp_user;

    if ( ! $action )
        $action = usp_get_time_user_action( $usp_user->ID );

    $last_action = usp_get_useraction( $action );

    $class = ( ! $last_action && $action) ? 'usp-online' : 'usp-offline';

    $content = apply_filters( 'usp_before_miniaction', '' );

    $content .= ( ! $last_action && $action) ? '<i class="uspi fa-circle usp-status-user ' . $class . '"></i>' : '<span class="usp-status-user usp-offline">' . __( 'offline', 'userspace' ) . ' ' . $last_action . '</span>';

    return $content;
}

// replace the link of the comment author with the link of his personal account
add_filter( 'get_comment_author_url', 'usp_get_link_author_comment', 10 );
function usp_get_link_author_comment( $url ) {
    global $comment;
    if ( ! isset( $comment ) || $comment->user_id == 0 )
        return $url;
    return usp_get_user_url( $comment->user_id );
}

function usp_is_register_open() {
    return apply_filters( 'usp_users_can_register', get_site_option( 'users_can_register' ) );
}

function usp_update_profile_fields( $user_id, $profileFields = false ) {
    global $user_ID;

    require_once(ABSPATH . "wp-admin" . '/includes/image.php');
    require_once(ABSPATH . "wp-admin" . '/includes/file.php');
    require_once(ABSPATH . "wp-admin" . '/includes/media.php');

    if ( ! $profileFields )
        $profileFields = usp_get_profile_fields();

    if ( $profileFields ) {

        $defaultFields = array(
            'user_email',
            'description',
            'user_url',
            'first_name',
            'last_name',
            'display_name',
            'primary_pass',
            'repeat_pass'
        );

        foreach ( $profileFields as $field ) {

            $field = apply_filters( 'usp_pre_update_profile_field', $field, $user_id );

            if ( ! $field || ! $field['slug'] )
                continue;

            $slug = $field['slug'];

            $value = (isset( $_POST[$slug] )) ? $_POST[$slug] : false;

            if ( isset( $field['admin'] ) && $field['admin'] == 1 && ! is_admin() && ! usp_is_user_role( $user_ID, [ 'administrator' ] ) ) {

                if ( in_array( $slug, array( 'display_name', 'user_url' ) ) ) {

                    if ( get_the_author_meta( $slug, $user_id ) )
                        continue;
                } else {

                    if ( get_user_meta( $user_id, $slug, $value ) )
                        continue;
                }
            }

            if ( $field['type'] == 'file' ) {

                $attach_id = get_user_meta( $user_id, $slug, 1 );

                if ( $attach_id && $value != $attach_id ) {
                    wp_delete_attachment( $attach_id );
                    delete_user_meta( $user_id, $slug );
                }
            }

            if ( $field['type'] != 'editor' ) {

                if ( is_array( $value ) ) {
                    $value = array_map( 'esc_html', $value );
                } else {
                    $value = esc_html( $value );
                }
            }

            if ( in_array( $slug, $defaultFields ) ) {

                if ( $slug == 'repeat_pass' )
                    continue;

                if ( $slug == 'primary_pass' && $value ) {

                    if ( $value != $_POST['repeat_pass'] )
                        continue;

                    $slug = 'user_pass';
                }

                if ( $slug == 'user_email' ) {

                    if ( ! $value )
                        continue;

                    $currentEmail = get_the_author_meta( 'user_email', $user_id );

                    if ( $currentEmail == $value )
                        continue;
                }

                wp_update_user( array( 'ID' => $user_id, $slug => $value ) );

                continue;
            }

            if ( $field['type'] == 'checkbox' ) {

                $vals = array();

                if ( is_array( $value ) ) {

                    $vals = array();

                    foreach ( $value as $val ) {
                        if ( in_array( $val, $field['values'] ) )
                            $vals[] = $val;
                    }
                }

                if ( $vals ) {
                    update_user_meta( $user_id, $slug, $vals );
                } else {
                    delete_user_meta( $user_id, $slug );
                }
            } else {

                if ( $value ) {

                    update_user_meta( $user_id, $slug, $value );
                } else {

                    if ( get_user_meta( $user_id, $slug, $value ) )
                        delete_user_meta( $user_id, $slug, $value );
                }
            }

            if ( $value ) {

                if ( $field['type'] == 'uploader' ) {
                    foreach ( $value as $val ) {
                        usp_delete_temp_media( $val );
                    }
                } else if ( $field['type'] == 'file' ) {
                    usp_delete_temp_media( $value );
                }
            }
        }
    }

    do_action( 'usp_update_profile_fields', $user_id );
}

function usp_get_profile_fields( $args = false ) {

    $fields = get_site_option( 'usp_profile_fields' );

    $fields = apply_filters( 'usp_profile_fields', $fields, $args );

    $profileFields = array();

    if ( $fields ) {

        foreach ( $fields as $k => $field ) {

            if ( isset( $args['include'] ) && ! in_array( $field['slug'], $args['include'] ) ) {

                continue;
            }

            if ( isset( $args['exclude'] ) && in_array( $field['slug'], $args['exclude'] ) ) {

                continue;
            }

            $profileFields[] = $field;
        }
    }

    return $profileFields;
}

function usp_get_profile_field( $field_id ) {

    $fields = usp_get_profile_fields( array( 'include' => array( $field_id ) ) );

    return $fields[0];
}

add_filter( 'author_link', 'usp_author_link', 999, 2 );
function usp_author_link( $link, $author_id ) {
    if ( usp_get_option( 'usp_profile_page_output', 'shortcode' ) == 'authorphp' )
        return $link;

    return usp_get_user_url( $author_id );
}

/**
 * Returns the url to the account specified by the user id
 * Takes into account the setting of the output of the user profile page
 *
 * @since 1.0
 *
 * @param int $user_id    id user.
 *
 * @return string   url to user.
 */
function usp_get_user_url( $user_id = false ) {
    global $user_ID;

    if ( ! $user_id )
        $user_id = $user_ID;

    if ( usp_get_option( 'usp_profile_page_output', 'shortcode' ) == 'authorphp' )
        return get_author_posts_url( $user_id );

    return add_query_arg(
        [
            usp_get_option( 'usp_user_account_slug', 'user' ) => $user_id
        ], get_permalink( usp_get_option( 'usp_user_account_page' ) )
    );
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
 * @since 1.0
 *
 * @param int   $user_id    id user.
 * @param array $args       $args['class'] add some class.
 *
 * @return string       user custom fields
 */
function usp_show_user_custom_fields( $user_id, $args = false ) {
    $get_fields = usp_get_profile_fields();

    if ( ! $get_fields )
        return;

    $content = '';

    USP()->use_module( 'fields' );

    foreach ( ( array ) stripslashes_deep( $get_fields ) as $field ) {
        $field = apply_filters( 'usp_custom_field_profile', $field );

        if ( ! $field )
            continue;

        $slug = isset( $field['name'] ) ? $field['name'] : $field['slug'];

        if ( isset( $field['req'] ) && $field['req'] ) {
            $field['public_value'] = $field['req'];
        }

        if ( isset( $field['public_value'] ) && $field['public_value'] == 1 ) {
            $field['value'] = get_the_author_meta( $slug, $user_id );

            $content .= USP_Field::setup( $field )->get_field_value( true );
        }
    }

    if ( ! $content )
        return;

    $class = ($args['class']) ? ' ' . $args['class'] : '';

    return '<div class="usp-user-fields ' . $class . ' usps usps__column">' . $content . '</div>';
}

add_action( 'usp_masonry_content', 'usp_masonry_age', 14 );
function usp_masonry_age() {
    global $usp_user;

    echo usp_get_user_age( $usp_user->ID, 'usp-masonry__age' );
}

add_action( 'usp_masonry_content', 'usp_masonry_description', 18 );
function usp_masonry_description() {
    global $usp_user;

    echo usp_get_user_description( $usp_user->ID, [ 'side' => 'top' ] );
}

add_action( 'usp_masonry_content', 'usp_masonry_custom_fields', 22 );
function usp_masonry_custom_fields() {
    echo '<div class="usp-masonry__fields">' . usp_get_user_custom_fields() . '</div>';
}

/**
 * gets a block of the number of comments of the specified user
 *
 * @since 1.0
 *
 * @param int       $user_id    id user
 *
 * @return string   html block
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
 * @since 1.0
 *
 * @param int       $user_id    id user
 *
 * @return string   html block
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
 * @since 1.0
 *
 * @param int       $user_id    id user
 *
 * @return string   html block
 */
function usp_user_get_date_registered( $user_id ) {
    if ( ! $user_id )
        return;

    $register_date = get_userdata( $user_id );

    $title = __( 'Registration', 'userspace' ) . ':';
    $count = mysql2date( 'd-m-Y', $register_date->user_registered );
    $icon  = 'fa-calendar-check';
    $class = 'usp-meta__reg';

    echo usp_user_get_stat_item( $title, $count, $icon, $class );
}
