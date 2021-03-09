<?php

class USP_Users_List extends USP_Users_Query {

    public $id;
    public $template    = 'rows';
    public $usergroup   = '';
    public $group_id    = '';
    public $only        = false;
    public $filters     = 0;
    public $search_form = 1;
    public $data;
    public $orderby     = 'time_action';
    public $add_uri;
    public $width;

    function __construct( $args = array() ) {

        if ( ! $args )
            $args = array();

        if ( isset( $args['inpage'] ) ) {
            $args['number'] = $args['inpage'];
        }

        if ( isset( $args['include'] ) ) {
            $args['ID__in'] = array_map( 'trim', explode( ',', $args['include'] ) );
        }

        if ( isset( $args['exclude'] ) ) {
            $args['ID__not_in'] = array_map( 'trim', explode( ',', $args['exclude'] ) );
        }

        parent::__construct();

        if ( $args )
            $this->init_properties( $args );

        $args['select'] = array(
            'ID',
            'display_name',
            'user_nicename'
        );

        $this->parse( $args );

        $this->data = ($this->data) ? array_map( 'trim', explode( ',', $this->data ) ) : array();

        if ( isset( $_GET['usergroup'] ) )
            $this->usergroup = $_GET['usergroup'];

        if ( $this->filters ) {

            if ( isset( $_GET['users-filter'] ) )
                $this->orderby = $_GET['users-filter'];

            if ( isset( $_GET['users-order'] ) )
                $this->query['order'] = $_GET['users-order'];

            add_filter( 'usp_users_query', array( $this, 'add_query_search' ) );
        }

        $this->add_uri['users-filter'] = $this->query['order'];

        add_filter( 'usp_users', array( $this, 'add_avatar_data' ) );

        if ( $this->data( 'description' ) )
            add_filter( 'usp_users', array( $this, 'add_descriptions' ) );

        if ( $this->data( 'profile_fields' ) )
            add_filter( 'usp_users', array( $this, 'add_profile_fields' ) );

        if ( $this->usergroup )
            add_filter( 'usp_users_query', array( $this, 'add_query_usergroup' ) );

        if ( $this->data( 'user_registered' ) || $this->orderby == 'user_registered' )
            add_filter( 'usp_users_query', array( $this, 'add_query_user_registered' ) );

        // getting the rating data
        if ( $this->orderby == 'rating_total' )
            add_filter( 'usp_users_query', array( $this, 'add_query_rating_total' ) );
        else if ( $this->data( 'rating_total' ) )
            add_filter( 'usp_users', array( $this, 'add_rating_total' ) );

        // count publications
        if ( $this->orderby == 'posts_count' )
            add_filter( 'usp_users_query', array( $this, 'add_query_posts_count' ) );
        else if ( $this->data( 'posts_count' ) )
            add_filter( 'usp_users', array( $this, 'add_posts_count' ) );

        // count comments
        if ( $this->orderby == 'comments_count' )
            add_filter( 'usp_users_query', array( $this, 'add_query_comments_count' ) );
        else if ( $this->data( 'comments_count' ) )
            add_filter( 'usp_users', array( $this, 'add_comments_count' ) );

        if ( $this->orderby == 'time_action' )
            add_filter( 'usp_users_query', array( $this, 'add_query_time_action' ) );
        else
            add_filter( 'usp_users', array( $this, 'add_time_action' ) );

        if ( $this->only == 'action_users' ) {
            add_filter( 'usp_users_query', array( $this, 'add_query_only_actions_users' ) );
        }

        $this->query = apply_filters( 'usp_users_query', $this->query );
    }

    function remove_filters() {
        remove_all_filters( 'usp_users_query' );
        remove_all_filters( 'usp_users' );
    }

    function init_properties( $args ) {

        $properties = get_class_vars( get_class( $this ) );

        foreach ( $properties as $name => $val ) {
            if ( isset( $args[$name] ) )
                $this->$name = $args[$name];
        }
    }

    function setup_userdata( $userdata ) {
        global $usp_user;

        $usp_user = ( object ) $userdata;

        return $usp_user;
    }

    function data( $needle ) {
        if ( ! $this->data )
            return false;
        $key = array_search( $needle, $this->data );
        return (false !== $key) ? true : false;
    }

    function get_users() {

        $users = apply_filters( 'usp_users', $this->get_data() );

        return $users;
    }

    function search_request() {
        global $user_LK;

        $rqst = '';

        if ( isset( $_GET['usergroup'] ) || isset( $_GET['search-user'] ) || $user_LK ) {
            $rqst = array();
            foreach ( $_GET as $k => $v ) {
                if ( $k == 'usp-page' || $k == 'users-filter' )
                    continue;
                $rqst[$k] = $k . '=' . $v;
            }
        }

        if ( $this->add_uri ) {
            foreach ( $this->add_uri as $k => $v ) {
                $rqst[$k] = $k . '=' . $v;
            }
        }

        $rqst = apply_filters( 'usp_users_uri', $rqst );

        return $rqst;
    }

    function add_query_only_actions_users( $query ) {

        $timeout          = usp_get_option( 'timeout', 10 );
        $query['where'][] = "actions.date_action > date_sub('" . current_time( 'mysql' ) . "', interval $timeout minute)";

        if ( $this->orderby != 'time_action' ) {
            $query['join'][] = "RIGHT JOIN " . USP_PREF . "users_actions AS actions ON wp_users.ID = actions.user_id";
        }

        return $query;
    }

    // add profile field data if listed via usergroup
    function add_query_usergroup( $query ) {
        global $wpdb;

        $usergroup = explode( '|', $this->usergroup );
        foreach ( $usergroup as $k => $filt ) {
            $f                = explode( ':', $filt );
            $n                = 'metas_' . str_replace( '-', '_', $f[0] );
            $query['join'][]  = "INNER JOIN $wpdb->usermeta AS $n ON wp_users.ID=$n.user_id";
            $query['where'][] = "($n.meta_key='$f[0]' AND $n.meta_value LIKE '%$f[1]%')";
        }

        return $query;
    }

    function add_profile_fields( $users ) {
        global $wpdb;

        $profile_fields = usp_get_profile_fields();

        $profile_fields = apply_filters( 'usp_userslist_custom_fields', $profile_fields );

        if ( ! $profile_fields )
            return $users;

        $profile_fields = stripslashes_deep( $profile_fields );

        $slugs  = array();
        $fields = array();

        foreach ( $profile_fields as $custom_field ) {
            $custom_field = apply_filters( 'usp_userslist_custom_field', $custom_field );
            if ( ! $custom_field )
                continue;

            if ( isset( $field['req'] ) && $field['req'] ) {
                $field['public_value'] = $field['req'];
            }

            if ( isset( $custom_field['public_value'] ) && $custom_field['public_value'] == 1 ) {
                $fields[] = $custom_field;
                $slugs[]  = $custom_field['slug'];
            }
        }

        if ( ! $fields )
            return $users;

        $ids = $this->get_users_ids( $users );

        $fielddata = array();
        foreach ( $fields as $k => $field ) {

            $fielddata[$field['slug']]['title'] = $field['title'];
            $fielddata[$field['slug']]['type']  = $field['type'];

            if ( isset( $field['filter'] ) )
                $fielddata[$field['slug']]['filter'] = $field['filter'];
        }

        $query = "SELECT meta_key,meta_value, user_id AS ID "
            . "FROM $wpdb->usermeta "
            . "WHERE user_id IN (" . implode( ',', $ids ) . ") AND meta_key IN ('" . implode( "','", $slugs ) . "')";

        $metas = $wpdb->get_results( $query );

        $newmetas = array();
        foreach ( $metas as $k => $meta ) {
            $newmetas[$meta->ID]['ID']                          = $meta->ID;
            $newmetas[$meta->ID]['profile_fields'][$k]['slug']  = $meta->meta_key;
            $newmetas[$meta->ID]['profile_fields'][$k]['value'] = maybe_unserialize( $meta->meta_value );
            $newmetas[$meta->ID]['profile_fields'][$k]['title'] = $fielddata[$meta->meta_key]['title'];
            $newmetas[$meta->ID]['profile_fields'][$k]['type']  = $fielddata[$meta->meta_key]['type'];

            if ( isset( $fielddata[$meta->meta_key]['filter'] ) )
                $newmetas[$meta->ID]['profile_fields'][$k]['filter'] = $fielddata[$meta->meta_key]['filter'];

            ( object ) $newmetas[$meta->ID];
        }

        if ( $newmetas )
            $users = $this->merge_objects( $users, $newmetas, 'profile_fields' );

        return $users;
    }

    function add_query_user_registered( $query ) {

        $query['select'][] = "wp_users.user_registered";

        if ( $this->orderby )
            $query['orderby'] = "wp_users.user_registered";

        return $query;
    }

    // add a selection of user activity data to the main query
    function add_query_time_action( $query ) {

        $query['select'][] = "actions.date_action";
        $query['orderby']  = "actions.date_action";

        $query['join'][] = "RIGHT JOIN " . USP_PREF . "users_actions AS actions ON wp_users.ID = actions.user_id";

        return $query;
    }

    // adding user activity data after the main query
    function add_time_action( $users ) {
        global $wpdb;

        $ids = $this->get_users_ids( $users );

        if ( $ids ) {

            $query = "SELECT date_action, user_id AS ID "
                . "FROM " . USP_PREF . "users_actions "
                . "WHERE user_id IN (" . implode( ',', $ids ) . ")";

            $posts = $wpdb->get_results( $query );

            if ( $posts )
                $users = $this->merge_objects( $users, $posts, 'date_action' );
        }

        return $users;
    }

    // adding a selection of these posts to the main query
    function add_query_posts_count( $query ) {
        global $wpdb;

        $query['select'][] = "posts.posts_count";
        $query['orderby']  = "posts.posts_count";

        $query['join'][] = "INNER JOIN (SELECT COUNT(post_author) AS posts_count, post_author "
            . "FROM $wpdb->posts "
            . "WHERE post_status IN ('publish', 'private') AND post_type NOT IN ('page','nav_menu_item') "
            . "GROUP BY post_author) posts "
            . "ON wp_users.ID = posts.post_author";

        return $query;
    }

    // adding publication data after the main query
    function add_posts_count( $users ) {
        global $wpdb;

        if ( ! $users )
            return $users;

        $ids = $this->get_users_ids( $users );

        $query = "SELECT COUNT(post_author) AS posts_count, post_author AS ID "
            . "FROM $wpdb->posts "
            . "WHERE post_status IN ('publish', 'private') AND post_type NOT IN ('page','nav_menu_item') AND post_author IN (" . implode( ',', $ids ) . ") "
            . "GROUP BY post_author";

        $posts = $wpdb->get_results( $query );

        if ( $posts )
            $users = $this->merge_objects( $users, $posts, 'posts_count' );

        return $users;
    }

    // adding a selection of these comments to the main query
    function add_query_comments_count( $query ) {
        global $wpdb;

        $query['select'][] = "comments.comments_count";
        $query['orderby']  = "comments.comments_count";

        $query['join'][] = "INNER JOIN (SELECT COUNT(user_id) AS comments_count, user_id "
            . "FROM $wpdb->comments "
            . "GROUP BY user_id) comments "
            . "ON wp_users.ID = comments.user_id";

        return $query;
    }

    // adding comment data after the main query
    function add_comments_count( $users ) {
        global $wpdb;

        if ( ! $users )
            return $users;

        $ids = $this->get_users_ids( $users );

        $query = "SELECT COUNT(user_id) AS comments_count, user_id AS ID "
            . "FROM $wpdb->comments "
            . "WHERE user_id IN (" . implode( ',', $ids ) . ") "
            . "GROUP BY user_id";

        $comments = $wpdb->get_results( $query );

        if ( $comments )
            $users = $this->merge_objects( $users, $comments, 'comments_count' );

        return $users;
    }

    // adding status data after the main query
    function add_descriptions( $users ) {
        global $wpdb;

        if ( ! $users )
            return $users;

        $ids = $this->get_users_ids( $users );

        $query = "SELECT meta_value AS description, user_id AS ID "
            . "FROM $wpdb->usermeta "
            . "WHERE user_id IN (" . implode( ',', $ids ) . ") AND meta_key='description'";

        $descs = $wpdb->get_results( $query );

        if ( $descs )
            $users = $this->merge_objects( $users, $descs, 'description' );

        return $users;
    }

    function add_avatar_data( $users ) {
        global $wpdb;

        if ( ! $users )
            return $users;

        $ids = $this->get_users_ids( $users );

        $query = "SELECT meta_value AS avatar_data, user_id AS ID "
            . "FROM $wpdb->usermeta "
            . "WHERE user_id IN (" . implode( ',', $ids ) . ") AND meta_key='usp_avatar'";

        $descs = $wpdb->get_results( $query );

        if ( $descs )
            $users = $this->merge_objects( $users, $descs, 'avatar_data' );

        return $users;
    }

    // adding a selection of rating data to the main query
    function add_query_rating_total( $query ) {

        $query['select'][] = "ratings.rating_total";
        $query['groupby']  = "ratings.user_id";
        $query['orderby']  = "CAST(ratings.rating_total AS DECIMAL)";

        $query['join'][] = "INNER JOIN " . USP_PREF . "rating_users AS ratings ON wp_users.ID = ratings.user_id";

        return $query;
    }

    // adding rating data after the main query
    function add_rating_total( $users ) {
        if ( ! in_array( 'userspace-rating-system/userspace-rating-system.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) )
            return $users;

        global $wpdb;

        if ( ! $users )
            return $users;

        $ids = $this->get_users_ids( $users );

        $query = "SELECT rating_total, user_id AS ID "
            . "FROM " . USP_PREF . "rating_users "
            . "WHERE user_id IN (" . implode( ',', $ids ) . ")";

        $descs = $wpdb->get_results( $query );

        if ( $descs )
            $users = $this->merge_objects( $users, $descs, 'rating_total' );

        return $users;
    }

    function get_users_ids( $users ) {

        if ( ! $users )
            return $users;

        $ids = array();

        foreach ( $users as $user ) {
            if ( ! isset( $user->ID ) || ! $user->ID )
                continue;
            $ids[] = $user->ID;
        }

        return $ids;
    }

    function merge_objects( $users, $data, $key ) {
        foreach ( $users as $k => $user ) {
            foreach ( $data as $d ) {
                if ( is_array( $d ) ) {
                    if ( $d['ID'] != $user->ID )
                        continue;
                    $users[$k]->$key = $d[$key];
                } else {
                    if ( $d->ID != $user->ID )
                        continue;
                    $users[$k]->$key = $d->$key;
                }
            }
        }
        return $users;
    }

    function get_filters( $num_users = false ) {
        global $post, $user_LK;

        if ( ! $this->filters )
            return false;

        $content = '';

        if ( $this->search_form )
            $content = apply_filters( 'usp_users_search_form', $content );

        $count_users = (false !== $num_users) ? $num_users : $this->count();

        $content .= '<div class="usp-users__title">' . __( 'Total number of users', 'userspace' ) . ': ' . $count_users . '</div>';

        if ( isset( $this->add_uri['users-filter'] ) )
            unset( $this->add_uri['users-filter'] );

        $s_array = $this->search_request();

        $rqst = ($s_array) ? implode( '&', $s_array ) . '&' : '';

        if ( usp_is_office() ) {
            $url = (isset( $_POST['tab_url'] )) ? $_POST['tab_url'] : usp_get_user_url( $user_LK );
        } else {
            $url = get_permalink( $post->ID );
        }

        $perm = usp_format_url( $url ) . $rqst;

        $current_filter = (isset( $_GET['users-filter'] )) ? $_GET['users-filter'] : 'time_action';

        $filters = array(
            'time_action'     => __( 'Activity', 'userspace' ),
            'posts_count'     => __( 'Publications', 'userspace' ),
            'comments_count'  => __( 'Comments', 'userspace' ),
            'user_registered' => __( 'Registration', 'userspace' ),
        );

        if ( function_exists( 'uspr_get_rating_users' ) )
            $filters['rating_total'] = __( 'Rated', 'userspace' );

        $filters = apply_filters( 'usp_users_filter', $filters );

        $content .= '<div class="usp-users__filter"><span>' . __( 'Filter by', 'userspace' ) . ':</span>';

        foreach ( $filters as $key => $name ) {
            $content .= usp_get_button( array(
                'label'  => $name,
                'href'   => $perm . 'users-filter=' . $key,
                'status' => $current_filter == $key ? 'disabled' : null
                ) );
        }

        $content .= '</div>';

        return $content;
    }

    function add_query_search( $query ) {
        $search_text  = (isset( $_GET['search_text'] )) ? sanitize_user( $_GET['search_text'] ) : '';
        $search_field = (isset( $_GET['search_field'] )) ? sanitize_key( $_GET['search_field'] ) : '';

        if ( ! $search_text || ! $search_field )
            return $query;

        if ( $search_field == 'usp_birthday' ) {
            global $wpdb;

            $query['join'][]  = "INNER JOIN $wpdb->usermeta AS wp_usermeta ON wp_users.ID=wp_usermeta.user_id";
            $query['where'][] = "wp_usermeta.meta_key LIKE '$search_field' AND wp_usermeta.meta_value LIKE '%$search_text%'";
        } else {
            $query['where'][] = "wp_users.$search_field LIKE '%$search_text%'";
        }

        return $query;
    }

}
