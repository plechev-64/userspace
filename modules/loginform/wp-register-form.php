<?php

//добавляем коды ошибок для тряски формы ВП
add_filter( 'shake_error_codes', 'usp_add_shake_error_codes' );
function usp_add_shake_error_codes( $codes ) {
    return array_merge( $codes, array(
        'usp_register_login',
        'usp_register_empty',
        'usp_register_email',
        'usp_register_login_us',
        'usp_register_email_us'
        ) );
}

add_filter( 'wp_login_errors', 'usp_checkemail_success' );
function usp_checkemail_success( $errors ) {

    if ( isset( $_GET['success'] ) && $_GET['success'] == 'checkemail' ) {

        $errors = new WP_Error();
        $errors->add( 'checkemail', __( 'Your email has been successfully confirmed! Log in using your username and password', 'userspace' ), 'message' );
    }

    if ( isset( $_GET['register'] ) ) {

        $errors = new WP_Error();

        if ( $_GET['register'] == 'success' ) {

            $errors->add( 'register', __( 'Registration completed!', 'userspace' ), 'message' );
        }

        if ( $_GET['register'] == 'checkemail' ) {

            $errors->add( 'register', __( 'Registration is completed! Check your email for the confirmation link.', 'userspace' ), 'message' );
        }
    }

    if ( isset( $_GET['login'] ) ) {

        $errors = new WP_Error();

        if ( $_GET['login'] == 'checkemail' ) {

            $errors->add( 'register', __( 'Your email is not confirmed!', 'userspace' ), 'error' );
        }
    }

    if ( isset( $_GET['remember'] ) ) {

        $errors = new WP_Error();

        if ( $_GET['remember'] == 'success' ) {

            $errors->add( 'register', __( 'Your password has been sent!<br>Check your email.', 'userspace' ), 'message' );
        }
    }

    return $errors;
}

add_action( 'register_form', 'usp_add_register_fields_to_register_form', 10 );
function usp_add_register_fields_to_register_form() {

    $fields = usp_get_register_form_fields();

    foreach ( $fields as $k => $field ) {
        if ( $field['slug'] == 'user_email' ) {
            unset( $fields[$k] );
        } else if ( $field['slug'] == 'user_login' ) {
            unset( $fields[$k] );
        }
    }

    USP()->use_module( 'forms' );

    $form = new USP_Form( [
        'fields' => $fields
        ] );

    echo $form->get_fields_list();
}

add_filter( 'login_redirect', 'usp_edit_default_login_redirect', 10, 3 );
function usp_edit_default_login_redirect( $redirect_to, $requested_redirect_to, $user ) {

    if ( is_wp_error( $user ) )
        return $redirect_to;

    usp_update_timeaction_user();

    return usp_get_authorize_url( $user->ID );
}
