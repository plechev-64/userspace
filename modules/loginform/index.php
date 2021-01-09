<?php

require_once 'registration.php';
require_once 'authorization.php';

if ( $GLOBALS['pagenow'] === 'wp-login.php' ) {
    require_once 'wp-register-form.php';
}

if ( class_exists( 'ReallySimpleCaptcha' ) ) {
    require_once 'captcha.php';
}

if ( is_admin() || isset( $_REQUEST['rest_route'] ) ) {
    usp_loginform_scripts();
} else {
    add_action( 'usp_enqueue_scripts', 'usp_loginform_scripts', 10 );
}
function usp_loginform_scripts() {
    if ( ! usp_get_option( 'login_form_recall' ) )
        usp_dialog_scripts();
    usp_enqueue_style( 'usp-loginform', USP_URL . 'modules/loginform/assets/style.css', false, false, true );
    usp_enqueue_script( 'usp-loginform', USP_URL . 'modules/loginform/assets/scripts.js', false, false, true );
}

function usp_get_loginform( $atts = [] ) {
    global $user_ID;

    extract( shortcode_atts( array(
        'active' => 'login',
        'forms'  => 'login,register,lostpassword'
            ), $atts ) );

    $forms = array_map( 'trim', explode( ',', $forms ) );

    $content = '<div class="usp-loginform preloader-parent">';

    $content .= '<div class="tab-group">';
    if ( in_array( 'login', $forms ) )
        $content .= '<a href="#login" class="tab tab-login' . ($active == 'login' ? ' active' : '') . '" onclick="USP.loginform.tabShow(\'login\',this);return false;">' . __( 'Авторизация', 'userspace' ) . '</a>';
    if ( in_array( 'register', $forms ) )
        $content .= '<a href="#register" class="tab tab-register' . ($active == 'register' ? ' active' : '') . '" onclick="USP.loginform.tabShow(\'register\',this);return false;">' . __( 'Регистрация', 'userspace' ) . '</a>';
    $content .= '</div>';

    $content .= apply_filters( 'usp_loginform_notice', '' );

    if ( in_array( 'login', $forms ) ) {

        $content .= '<div class="tab-content tab-login' . ($active == 'login' ? ' active' : '') . '">';

        $content .= usp_get_form( array(
            'submit'  => __( 'Вход', 'userspace' ),
            'onclick' => 'USP.loginform.send("login",this);return false;',
            'fields'  => apply_filters( 'usp_login_form_fields', array(
                array(
                    'slug'        => 'user_login',
                    'type'        => 'text',
                    'title'       => __( 'Логин или E-mail', 'userspace' ),
                    'placeholder' => __( 'Логин или E-mail', 'userspace' ),
                    'icon'        => 'fa-user',
                    'maxlenght'   => 50,
                    'required'    => 1
                ),
                array(
                    'slug'        => 'user_pass',
                    'type'        => 'password',
                    'title'       => __( 'Password', 'userspace' ),
                    'placeholder' => __( 'Password', 'userspace' ),
                    'icon'        => 'fa-key',
                    'maxlenght'   => 50,
                    'required'    => 1
                )
            ) )
            ) );

        if ( in_array( 'lostpassword', $forms ) )
            $content .= '<a href="#" class="forget-link" onclick="USP.loginform.tabShow(\'lostpassword\',this);return false;">' . __( 'Forgot password?', 'userspace' ) . '</a>';

        $content .= '</div>';
    }

    if ( in_array( 'register', $forms ) ) {

        $content .= '<div class="tab-content tab-register' . ($active == 'register' ? ' active' : '') . '">';
        $content .= usp_get_form( array(
            'submit'    => __( 'Регистрация', 'userspace' ),
            'onclick'   => 'USP.loginform.send("register",this);return false;',
            'fields'    => usp_get_register_form_fields(),
            'structure' => get_site_option( 'usp_fields_register_form_structure' )
            )
        );
        $content .= '</div>';
    }

    if ( in_array( 'lostpassword', $forms ) ) {
        $content .= '<div class="tab-content tab-lostpassword' . ($active == 'lostpassword' ? ' active' : '') . '">';
        $content .= usp_get_form( array(
            'submit'  => __( 'Получить новый пароль', 'userspace' ),
            'onclick' => 'USP.loginform.send("lostpassword",this);return false;',
            'fields'  => apply_filters( 'usp_lostpassword_form_fields', array(
                array(
                    'type'        => 'text',
                    'slug'        => 'user_login',
                    'title'       => __( 'Логин', 'userspace' ),
                    'placeholder' => __( 'Логин или Email', 'userspace' ),
                    'icon'        => 'fa-user',
                    'maxlenght'   => 50,
                    'required'    => 1
                )
                )
            ) )
        );
        $content .= '</div>';
    }

    $content .= '</div>';

    return $content;
}

function usp_get_loginform_url( $type ) {

    if ( $type == 'login' ) {
        switch ( usp_get_option( 'login_form_recall' ) ) {
            case 1: return add_query_arg( [ 'usp-form' => 'login' ], get_permalink( usp_get_option( 'page_login_form_recall' ) ) );
                break;
            case 2: return wp_login_url( get_permalink( usp_get_option( 'page_login_form_recall' ) ) );
                break;
            default: return '#';
                break;
        }
    }

    if ( $type == 'register' ) {
        switch ( usp_get_option( 'login_form_recall' ) ) {
            case 1: return add_query_arg( [ 'usp-form' => 'register' ], get_permalink( usp_get_option( 'page_login_form_recall' ) ) );
                break;
            case 2: return wp_registration_url();
                break;
            default: return '#';
                break;
        }
    }
}

//вызываем форму входа и регистрации
usp_ajax_action( 'usp_call_loginform', true );
function usp_call_loginform() {
    global $user_ID;

    $form = $_POST['form'];

    if ( $user_ID )
        return [
            'error' => __( 'Вы уже авторизованы!', 'userspace' )
        ];

    return [
        'dialog' => [
            'size'    => 'smallToMedium',
            'content' => usp_get_loginform( [ 'active' => $form ] )
        ]
    ];
}

//принимаем данные отправленные с формы входа и регистрации
usp_ajax_action( 'usp_send_loginform', true );
function usp_send_loginform() {

    $tab_id     = $_POST['tab_id'];
    $user_login = isset( $_POST['user_login'] ) ? sanitize_user( $_POST['user_login'] ) : false;

    if ( $tab_id == 'login' ) {

        $password = sanitize_text_field( $_POST['user_pass'] );

        $user = wp_signon( array(
            'user_login'    => $user_login,
            'user_password' => $password,
            'remember'      => isset( $_POST['remember'] ) ? true : false,
            ) );

        if ( is_wp_error( $user ) ) {
            return array(
                'error' => $user->get_error_message()
            );
        }

        usp_update_timeaction_user();

        return array(
            'redirect' => usp_get_authorize_url( $user->ID ),
            'success'  => __( 'Успешная авторизация', 'userspace' )
        );
    } else if ( $tab_id == 'register' ) {

        $user_email = sanitize_email( $_POST['user_email'] );

        if ( ! $user_login )
            $user_login = $user_email;

        $user_id = register_new_user( $user_login, $user_email );

        if ( is_wp_error( $user_id ) ) {
            return array(
                'error' => $user_id->get_error_message()
            );
        }

        return array(
            'content' => usp_get_notice( [
                'type' => 'success',
                'text' => __( 'Регистрация завершена, проверьте вашу почту, затем '
                    . 'зайдите на <a href="#" onclick="USP.loginform.tabShow(\'login\',this);'
                    . 'return false;">страницу входа</a>.', 'userspace' )
            ] ),
            'success' => __( 'Успешная регистрация', 'userspace' )
        );
    } else if ( $tab_id == 'lostpassword' ) {

        $result = retrieve_password();

        if ( is_wp_error( $result ) ) {
            return array(
                'error' => $result->get_error_message()
            );
        }

        return array(
            'content' => usp_get_notice( [
                'type' => 'success',
                'text' => __( 'Ссылка для восстановления пароля выслана на почту', 'userspace' )
            ] ),
            'success' => __( 'Письмо успешно отправлено', 'userspace' )
        );
    }
}

add_filter( 'usp_loginform_notice', 'usp_add_login_form_notice', 10 );
function usp_add_login_form_notice( $notice ) {

    if ( ! isset( $_REQUEST['formaction'] ) || ! $_REQUEST['formaction'] )
        return $notice;

    switch ( $_REQUEST['formaction'] ) {
        case 'success-checkemail':
            $notice = usp_get_notice( [
                'success' => __( 'Your email has been successfully confirmed! Log in using your username and password', 'userspace' )
                ] );
            break;
    }

    return $notice;
}

add_filter( 'usp_login_form_fields', 'usp_add_login_form_custom_data', 10 );
function usp_add_login_form_custom_data( $fields ) {

    ob_start();

    do_action( 'login_form' );

    $content = ob_get_contents();
    ob_end_clean();

    if ( ! $content )
        return $fields;

    $fields[] = array(
        'slug'    => 'custom_data',
        'type'    => 'custom',
        'content' => $content
    );

    return $fields;
}

add_filter( 'usp_login_form_fields', 'usp_add_rememberme_button', 20 );
function usp_add_rememberme_button( $fields ) {

    $fields[] = array(
        'slug'   => 'rememberme',
        'type'   => 'checkbox',
        'icon'   => 'fa-key',
        'values' => array(
            1 => __( 'Запомнить меня', 'userspace' )
        )
    );

    return $fields;
}

if ( $GLOBALS['pagenow'] !== 'wp-login.php' )
    add_filter( 'usp_register_form_fields', 'usp_add_register_form_custom_data', 10 );
function usp_add_register_form_custom_data( $fields ) {

    ob_start();

    do_action( 'register_form' );

    $content = ob_get_contents();
    ob_end_clean();

    if ( ! $content )
        return $fields;

    $fields[] = array(
        'slug'    => 'custom_data',
        'type'    => 'custom',
        'content' => $content
    );

    return $fields;
}

add_filter( 'usp_lostpassword_form_fields', 'usp_add_lostpassword_form_custom_data', 10 );
function usp_add_lostpassword_form_custom_data( $fields ) {

    ob_start();

    do_action( 'lostpassword_form' );

    $content = ob_get_contents();
    ob_end_clean();

    if ( ! $content )
        return $fields;

    $fields[] = array(
        'slug'    => 'custom_data',
        'type'    => 'custom',
        'content' => $content
    );

    return $fields;
}
