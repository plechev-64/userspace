<?php

function usp_get_register_form_fields() {

    $registerFields = array(
        array(
            'type'        => 'text',
            'slug'        => 'user_email',
            'title'       => __( 'Email', 'userspace' ),
            'placeholder' => __( 'Email', 'userspace' ),
            'icon'        => 'fa-at',
            'maxlenght'   => 50,
            'required'    => 1
        )
    );

    if ( $customFields = get_site_option( 'usp_register_form_fields' ) ) {
        $registerFields = array_merge( $registerFields, $customFields );
    }

    return apply_filters( 'usp_register_form_fields', $registerFields );
}

/* * * регистрация через функцию register_new_user() ** */
//отключаем регистрационное стандартное письмо с данными для входа
remove_action( 'register_new_user', 'wp_send_new_user_notifications' );
//отправляем письмо о регистрации от плагина
add_action( 'register_new_user', 'usp_process_user_register_data', 10 );
function usp_process_user_register_data( $user_id ) {

    $user_pass = isset( $_POST['user_pass'] ) ? $_POST['user_pass'] : false;

    if ( ! $user_pass ) {
        $user_pass = wp_generate_password( 12, false );
    }

    //отключаем отправку письма о смене пароля
    add_filter( 'send_password_change_email', function() {
        return false;
    } );

    wp_update_user( [
        'ID'        => $user_id,
        'user_pass' => $user_pass
    ] );

    usp_register_mail( array(
        'user_id'    => $user_id,
        'user_pass'  => $user_pass,
        'user_login' => isset( $_POST['user_login'] ) ? $_POST['user_login'] : $_POST['user_email'],
        'user_email' => $_POST['user_email']
    ) );

    wp_send_new_user_notifications( $user_id, 'admin' );

    if ( ! isset( $_REQUEST['rest_route'] ) ) {
        //если данные пришли с формы WP, то возвращаем на wp-login.php с нужными GET-параметрами
        if ( usp_get_option( 'usp_confirm_register' ) == 1 ) {
            wp_safe_redirect( wp_login_url() . '?checkemail=confirm' );
        } else {
            wp_safe_redirect( wp_login_url() . '?checkemail=registered' );
        }

        exit();
    }
}

//сохраняем данные пользователя при создании/регистрации
add_action( 'user_register', 'usp_register_new_user_data', 10 );
function usp_register_new_user_data( $user_id ) {

    $timeAction = '0000-00-00 00:00:00';

    if ( usp_get_option( 'usp_confirm_register' ) ) {
        wp_update_user( array(
            'ID'   => $user_id,
            'role' => 'need-confirm'
        ) );
    } else {
        $timeAction = current_time( 'mysql' );
    }

    global $wpdb;

    $wpdb->insert( USP_PREF . 'users_actions', array(
        'user'        => $user_id,
        'time_action' => $timeAction
    ) );

    update_user_meta( $user_id, 'show_admin_bar_front', 'false' );

    usp_update_profile_fields( $user_id );
}

/* * * регистрация через функцию register_new_user() конец ** */
function usp_insert_user( $data ) {

    if ( get_user_by( 'email', $data['user_email'] ) )
        return false;

    if ( get_user_by( 'login', $data['user_login'] ) )
        return false;

    $userdata = array_merge( $data, array(
        'user_nicename' => ''
        , 'nickname'      => $data['user_email']
        , 'first_name'    => $data['display_name']
        , 'rich_editing'  => 'true'  // false - выключить визуальный редактор для пользователя.
        ) );

    $user_id = wp_insert_user( $userdata );

    if ( ! $user_id || is_wp_error( $user_id ) ) {
        return false;
    }

    usp_register_new_user_data( $user_id );

    usp_register_mail( array(
        'user_id'    => $user_id,
        'user_login' => isset( $_POST['user_login'] ) ? $_POST['user_login'] : $_POST['user_email'],
        'user_email' => $_POST['user_email']
    ) );

    wp_send_new_user_notifications( $user_id, 'admin' );

    do_action( 'usp_insert_user', $user_id, $userdata );

    return $user_id;
}

//принимаем данные для подтверждения регистрации
add_action( 'init', 'usp_confirm_user_resistration_activate', 10 );
function usp_confirm_user_resistration_activate() {

    if ( ! isset( $_GET['usp-confirmdata'] ) )
        return false;

    if ( usp_get_option( 'usp_confirm_register' ) )
        add_action( 'wp', 'usp_confirm_user_registration', 10 );
}

//подтверждаем регистрацию пользователя по ссылке
function usp_confirm_user_registration() {
    global $wpdb;

    $type_form = usp_get_option( 'usp_login_form', 0 );

    if ( $confirmdata = urldecode( $_GET['usp-confirmdata'] ) ) {

        $confirmdata = json_decode( base64_decode( $confirmdata ) );

        if ( $user = get_user_by( 'login', $confirmdata[0] ) ) {

            if ( md5( $user->ID ) != $confirmdata[1] )
                return false;

            if ( ! usp_is_user_role( $user->ID, 'need-confirm' ) )
                return false;

            $defaultRole = get_site_option( 'default_role' );
            if ( $defaultRole == 'need-confirm' ) {
                update_site_option( 'default_role', 'author' );
                $defaultRole = 'author';
            }

            wp_update_user( array( 'ID' => $user->ID, 'role' => $defaultRole ) );

            if ( ! usp_get_time_user_action( $user->ID ) )
                $wpdb->insert( USP_PREF . 'users_actions', array( 'user_id' => $user->ID, 'date_action' => current_time( 'mysql' ) ) );

            do_action( 'usp_confirm_registration', $user->ID );

            //если используется форма WP
            if ( usp_get_option( 'usp_login_form' ) == 2 ) {
                wp_safe_redirect( wp_login_url() . '?success=checkemail' );
            } else {
                wp_redirect( add_query_arg( array(
                    'usp-form'   => 'login',
                    'type-form'  => ! $type_form ? 'float' : 'onpage',
                    'formaction' => 'success-checkemail'
                        ), ($type_form == 1 ? get_permalink( usp_get_option( 'usp_id_login_page' ) ) : home_url() ) ) );
            }
            exit;
        }
    }

    if ( usp_get_option( 'usp_login_form' ) == 2 ) {
        wp_safe_redirect( wp_login_url() . '?checkemail=confirm' );
    } else {
        wp_redirect( add_query_arg( array(
            'usp-form'   => 'login',
            'type-form'  => ! $type_form ? 'float' : 'onpage',
            'formaction' => 'need-checkemail'
                ), ($type_form == 1 ? get_permalink( usp_get_option( 'usp_id_login_page' ) ) : home_url() ) ) );
    }
    exit;
}

//ошибки плагина при регистрации
add_filter( 'registration_errors', 'usp_add_user_register_errors', 10 );
function usp_add_user_register_errors( $errors ) {

    $fields = usp_get_register_form_fields();

    if ( $fields ) {
        $required = true;
        foreach ( $fields as $field ) {
            if ( ! isset( $field['required'] ) || ! $field['required'] )
                continue;

            $slug = $field['slug'];

            if ( ! isset( $_POST[$slug] ) || ! $_POST[$slug] ) {
                $required = false;
                break;
            }
        }
    }

    if ( ! $required ) {
        $errors->add( 'usp_register_empty', __( 'Fill in the required fields!', 'userspace' ) );
    }

    if ( isset( $_POST['user_pass'] ) && isset( $_POST['user_pass_repeat'] ) && $_POST['user_pass'] != $_POST['user_pass_repeat'] ) {
        $errors->add( 'usp_register_repeat_pass', __( 'Repeated password not correct!', 'userspace' ) );
    }

    return $errors;
}

//письмо высылаемое при регистрации
function usp_register_mail( $userdata ) {

    $user_login = $userdata['user_login'];
    $user_id    = $userdata['user_id'];

    $userdata = apply_filters( 'usp_register_mail_data', $userdata );

    $textmail = '
    <p>' . __( 'You or someone else signed up on our website', 'userspace' ) . ' "' . get_bloginfo( 'name' ) . '" ' . __( 'with the following data:', 'userspace' ) . '</p>
    <p>' . __( 'Login', 'userspace' ) . ': ' . $userdata['user_login'] . '</p>
    <p>' . __( 'Password', 'userspace' ) . ': ' . $userdata['user_pass'] . '</p>';

    if ( usp_get_option( 'usp_confirm_register' ) ) {

        $subject = __( 'Confirm your registration!', 'userspace' );

        $confirmstr = base64_encode(
            json_encode(
                array(
                    $user_login,
                    md5( $user_id )
                )
            )
        );

        $url = add_query_arg( array(
            'usp-confirmdata' => urlencode( $confirmstr )
            ), home_url() );

        $textmail .= '<p>' . __( 'If it was you, then confirm your registration by clicking on the link below', 'userspace' ) . ':</p>
        <p><a href="' . $url . '">' . $url . '</a></p>
        <p>' . __( 'Unable to activate the account?', 'userspace' ) . '</p>
        <p>' . __( 'Copy the link below, paste it into the address bar of your browser and hit Enter', 'userspace' ) . '</p>';
    } else {

        $subject = __( 'Registration completed', 'userspace' );
    }

    $textmail .= '<p>' . __( 'If it wasn’t you, then just ignore this email', 'userspace' ) . '</p>';

    $textmail = apply_filters( 'usp_register_mail_text', $textmail, $userdata );

    usp_mail( $userdata['user_email'], $subject, $textmail );
}
