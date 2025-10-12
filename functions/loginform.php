<?php

function usp_get_loginform_url( $type ) {

    if ( $type == 'login' ) {
        switch ( usp_get_option( 'usp_login_form' ) ) {
            case 1:
                return add_query_arg( [ 'usp-form' => 'login' ], get_permalink( usp_get_option( 'usp_id_login_page' ) ) );
                break;
            case 2:
                return wp_login_url( get_permalink( usp_get_option( 'usp_id_login_page' ) ) );
                break;
            default:
                return '#';
                break;
        }
    }

    if ( $type == 'register' ) {
        switch ( usp_get_option( 'usp_login_form' ) ) {
            case 1:
                return add_query_arg( [ 'usp-form' => 'register' ], get_permalink( usp_get_option( 'usp_id_login_page' ) ) );
                break;
            case 2:
                return wp_registration_url();
                break;
            default:
                return '#';
                break;
        }
    }
}
