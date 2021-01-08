<?php
function usp_action() {
    global $usp_userlk_action;
    $last_action = usp_get_useraction( $usp_userlk_action );
    $class       = ( ! $last_action) ? 'online' : 'offline';

    if ( $last_action )
        $status = __( 'offline', 'userspace' ) . ' ' . $last_action;
    else
        $status = __( 'online', 'userspace' );

    echo sprintf( '<span class="user-status %s">%s</span>', $class, $status );
}

function usp_avatar( $avatar_size = 120, $attr = false ) {
    global $user_LK;
    ?>
    <div id="usp-avatar">
        <span class="avatar-image">
            <?php echo get_avatar( $user_LK, $avatar_size, false, false, $attr ); ?>
            <span id="avatar-upload-progress"><span></span></span>
        </span>
        <?php do_action( 'usp_avatar' ); ?>
    </div>
    <?php
}

function usp_status_desc() {
    global $user_LK;
    $desc = get_the_author_meta( 'description', $user_LK );
    if ( $desc )
        echo '<div class="ballun-status">'
        . '<div class="status-user-usp">' . nl2br( wp_strip_all_tags( $desc ) ) . '</div>'
        . '</div>';
}

function usp_username() {
    global $user_LK;
    echo get_the_author_meta( 'display_name', $user_LK );
}

function usp_user_name() {
    global $usp_user;
    echo $usp_user->display_name;
}

function usp_user_url() {
    global $usp_user;
    echo usp_get_user_url( $usp_user->ID );
}

function usp_user_avatar( $size = 50 ) {
    global $usp_user;
    echo get_avatar( $usp_user->ID, $size );
}

function usp_user_rayting() {
    global $usp_user, $usp_users_set;
    if ( ! usp_exist_addon( 'rating-system' ) )
        return false;
    if ( false !== array_search( 'rating_total', $usp_users_set->data ) || isset( $usp_user->rating_total ) ) {
        if ( ! isset( $usp_user->rating_total ) )
            $usp_user->rating_total = 0;
        echo uspr_rating_block( array( 'value' => $usp_user->rating_total ) );
    }
}

add_action( 'usp_user_description', 'usp_user_meta', 30 );
function usp_user_meta() {
    global $usp_user, $usp_users_set;
    if ( false !== array_search( 'profile_fields', $usp_users_set->data ) || isset( $usp_user->profile_fields ) ) {
        if ( ! isset( $usp_user->profile_fields ) )
            $usp_user->profile_fields = array();

        if ( $usp_user->profile_fields ) {

            echo '<div class="user-profile-fields">';
            foreach ( $usp_user->profile_fields as $field_id => $field ) {
                echo USP_Field::setup( $field )->get_field_value( 'title' );
            }
            echo '</div>';
        }
    }
}

add_action( 'usp_user_description', 'usp_user_comments', 20 );
function usp_user_comments() {
    global $usp_user, $usp_users_set;
    if ( false !== array_search( 'comments_count', $usp_users_set->data ) || isset( $usp_user->comments_count ) ) {
        if ( ! isset( $usp_user->comments_count ) )
            $usp_user->comments_count = 0;
        echo '<span class="filter-data"><i class="uspi fa-comment"></i>' . __( 'Comments', 'userspace' ) . ': ' . $usp_user->comments_count . '</span>';
    }
}

add_action( 'usp_user_description', 'usp_user_posts', 20 );
function usp_user_posts() {
    global $usp_user, $usp_users_set;
    if ( false !== array_search( 'posts_count', $usp_users_set->data ) || isset( $usp_user->posts_count ) ) {
        if ( ! isset( $usp_user->posts_count ) )
            $usp_user->posts_count = 0;
        echo '<span class="filter-data"><i class="uspi fa-file"></i>' . __( 'Publics', 'userspace' ) . ': ' . $usp_user->posts_count . '</span>';
    }
}

function usp_user_action( $type = 1 ) {
    global $usp_user;

    $action = (isset( $usp_user->time_action )) ? $usp_user->time_action : $usp_user->user_registered;

    switch ( $type ) {
        case 1: $last_action = usp_get_useraction( $action );
            if ( ! $last_action )
                echo '<span class="status_user online"><i class="uspi fa-circle"></i></span>';
            else
                echo '<span class="status_user offline" title="' . __( 'offline', 'userspace' ) . ' ' . $last_action . '"><i class="uspi fa-circle"></i></span>';
            break;
        case 2: echo usp_get_miniaction( $action );
            break;
    }
}

function usp_user_description() {
    global $usp_user;

    if ( isset( $usp_user->description ) && $usp_user->description ) {
        echo '<div class="ballun-status">';
        echo '<div class="status-user-usp">' . nl2br( esc_html( $usp_user->description ) ) . '</div>
		</div>';
    }

    do_action( 'usp_user_description' );
}

add_action( 'usp_user_description', 'usp_user_register', 20 );
function usp_user_register() {
    global $usp_user, $usp_users_set;
    if ( false !== array_search( 'user_registered', $usp_users_set->data ) || isset( $usp_user->user_registered ) ) {
        if ( ! isset( $usp_user->user_registered ) )
            return false;
        echo '<span class="filter-data"><i class="uspi fa-calendar-check"></i>' . __( 'Registration', 'userspace' ) . ': ' . mysql2date( 'd-m-Y', $usp_user->user_registered ) . '</span>';
    }
}

add_action( 'usp_user_description', 'usp_filter_user_description', 10 );
function usp_filter_user_description() {
    global $usp_user;
    $cont = '';
    echo $cont = apply_filters( 'usp_description_user', $cont, $usp_user->ID );
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

function usp_get_useraction( $user_action = false ) {
    global $usp_userlk_action;

    if ( ! $user_action )
        $user_action = $usp_userlk_action;

    $unix_time_user = strtotime( $user_action );

    if ( ! $unix_time_user || $user_action == '0000-00-00 00:00:00' )
        return __( 'long ago', 'userspace' );

    $timeout          = ($time             = usp_get_option( 'timeout' )) ? $time * 60 : 600;
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
                return '<span class="status_user online"><i class="uspi fa-circle"></i></span>';
            else
                return '<span class="status_user offline" title="' . __( 'offline', 'userspace' ) . ' ' . $last_action . '"><i class="uspi fa-circle"></i></span>';

            break;
        case 2:

            return usp_get_miniaction( $action );

            break;
    }
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

function usp_get_time_user_action( $user_id ) {

    $cachekey = json_encode( array( 'usp_get_time_user_action', ( int ) $user_id ) );
    $cache    = wp_cache_get( $cachekey );
    if ( $cache )
        return $cache;

    $action = RQ::tbl( new USP_User_Action() )->select( [ 'date_action' ] )->where( [ 'user_id' => $user_id ] )->get_var();

    if ( ! $action ) {
        $action = '0000-00-00 00:00:00';
    }

    wp_cache_add( $cachekey, $action, 'default', usp_get_option( 'timeout', 10 ) * 60 );

    return $action;
}

function usp_get_miniaction( $action ) {
    global $usp_user;

    if ( ! $action )
        $action = usp_get_time_user_action( $usp_user->ID );

    $last_action = usp_get_useraction( $action );

    $class = ( ! $last_action && $action) ? 'online' : 'offline';

    $content = apply_filters( 'usp_before_miniaction', '' );

    $content .= ( ! $last_action && $action) ? '<i class="uspi fa-circle"></i>' : __( 'offline', 'userspace' ) . ' ' . $last_action;

    $content = sprintf( '<div class="status_author_mess %s">%s</div>', $class, $content );

    return $content;
}

//заменяем ссылку автора комментария на ссылку его ЛК
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

/* 16.0.0 */
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

/* 16.0.0 */
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

    if ( usp_get_option( 'view_user_lk_usp' ) != 1 )
        return $link;

    return usp_get_user_url( $author_id );
}

function usp_get_user_url( $user_id = false ) {
    global $user_ID;

    if ( ! $user_id )
        $user_id = $user_ID;

    if ( usp_get_option( 'view_user_lk_usp' ) != 1 )
        return get_author_posts_url( $user_id );

    return add_query_arg(
        array(
            usp_get_option( 'link_user_lk_usp', 'user' ) => $user_id
        ), get_permalink( usp_get_option( 'lk_page_usp' ) )
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
