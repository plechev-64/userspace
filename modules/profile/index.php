<?php

//if ( is_admin() ) {
require_once 'admin/index.php';
//}

if ( ! is_admin() ):
    add_action( 'usp_enqueue_scripts', 'usp_profile_scripts', 10 );
endif;
function usp_profile_scripts() {
    global $user_ID;

    if ( ! usp_is_office( $user_ID ) )
        return;

    usp_enqueue_style( 'usp-profile', plugins_url( 'style.css', __FILE__ ) );
    usp_enqueue_script( 'usp-profile-scripts', plugins_url( 'js/scripts.js', __FILE__ ) );
}

add_filter( 'usp_init_js_variables', 'usp_init_js_profile_variables', 10 );
function usp_init_js_profile_variables( $data ) {
    global $user_ID;

    if ( ! usp_is_office( $user_ID ) )
        return $data;

    $data['local']['no_repeat_pass'] = __( 'Repeated password not correct!', 'userspace' );

    return $data;
}

add_action( 'usp_init_tabs', 'usp_tab_profile' );
function usp_tab_profile() {

    usp_tab(
        array(
            'id'       => 'profile',
            'name'     => __( 'Profile', 'userspace' ),
            'title'    => __( 'User profile', 'userspace' ),
            'supports' => array( 'ajax' ),
            'public'   => 0,
            'icon'     => 'fa-user',
            'content'  => array(
                array(
                    'callback' => array(
                        'name' => 'usp_tab_profile_content'
                    )
                )
            )
        )
    );
}

add_action( 'usp_bar_setup', 'usp_bar_add_profile_link', 10 );
function usp_bar_add_profile_link() {
    global $user_ID;

    if ( ! is_user_logged_in() )
        return false;

    usp_bar_add_menu_item( 'profile-link', array(
        'url'   => usp_get_tab_permalink( $user_ID, 'profile' ),
        'icon'  => 'fa-user-secret',
        'label' => __( 'Profile settings', 'userspace' )
        )
    );
}

//add_action( 'init', 'usp_add_block_show_profile_fields' );
function usp_add_block_show_profile_fields() {
    usp_block( 'details', 'usp_show_custom_fields_profile', array( 'id' => 'pf-block', 'order' => 20, 'public' => 1 ) );
}

function usp_show_custom_fields_profile( $master_id ) {

    $get_fields = usp_get_profile_fields();

    $content = '';

    if ( $get_fields ) {

        USP()->use_module( 'fields' );

        foreach ( ( array ) stripslashes_deep( $get_fields ) as $field ) {
            $field = apply_filters( 'custom_field_profile', $field );
            if ( ! $field )
                continue;
            $slug  = isset( $field['name'] ) ? $field['name'] : $field['slug'];

            if ( isset( $field['req'] ) && $field['req'] ) {
                $field['public_value'] = $field['req'];
            }

            if ( isset( $field['public_value'] ) && $field['public_value'] == 1 ) {
                $field['value'] = get_the_author_meta( $slug, $master_id );
                $content        .= USP_Field::setup( $field )->get_field_value( true );
            }
        }
    }

    if ( ! $content )
        return false;

    return '<div class="show-profile-fields">' . $content . '</div>';
}

if ( ! is_admin() )
    add_action( 'wp', 'usp_update_profile_notice' );
function usp_update_profile_notice() {
    if ( isset( $_GET['updated'] ) )
        add_action( 'usp_area_notice', function() {
            echo usp_get_notice( [ 'type' => 'success', 'text' => __( 'Your profile has been updated', 'userspace' ) ] );
        } );
}

//Обновляем профиль пользователя
add_action( 'wp', 'usp_edit_profile', 10 );
function usp_edit_profile() {
    global $user_ID;

    if ( ! isset( $_POST['submit_user_profile'] ) )
        return false;

    if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'update-profile_' . $user_ID ) )
        return false;

    usp_update_profile_fields( $user_ID );

    do_action( 'personal_options_update', $user_ID );

    $redirect_url = usp_get_tab_permalink( $user_ID, 'profile' ) . '&updated=true';

    wp_redirect( $redirect_url );

    exit;
}

add_filter( 'usp_profile_fields', 'usp_add_office_profile_fields', 10 );
function usp_add_office_profile_fields( $fields ) {

    if ( ! usp_check_access_console() )
        return $fields;

    $profileFields = [
        [
            'slug'    => 'show_admin_bar_front',
            'title'   => __( 'Admin toolbar', 'userspace' ),
            'type'    => 'radio',
            'values'  => [
                'false' => __( 'Disabled', 'userspace' ),
                'true'  => __( 'Enabled', 'userspace' )
            ],
            'default' => 'false',
        ]
    ];

    $fields = ($fields) ? array_merge( $profileFields, $fields ) : $profileFields;

    return $fields;
}

function usp_tab_profile_content( $master_id ) {
    global $userdata, $user_ID;

    USP()->use_module( 'forms' );

    $profileFields = usp_get_profile_fields( array( 'user_id' => $master_id ) );

    foreach ( $profileFields as $k => $field ) {

        $slug = $field['slug'];

        $profileFields[$k]['value'] = isset( $userdata->$slug ) ? $userdata->$slug : false;

        if ( $slug == 'email' )
            $profileFields[$k]['value'] = get_the_author_meta( 'email', $user_ID );

        if ( $slug != 'show_admin_bar_front' && ! isset( $field['value_in_key'] ) )
            $profileFields[$k]['value_in_key'] = true;

        if ( isset( $field['admin'] ) && $field['admin'] && ! usp_is_user_role( $user_ID, 'administrator' ) ) {
            if ( $profileFields[$k]['value'] ) {
                $profileFields[$k]['get_value'] = 1;
            }
        }
    }

    $profileFields[] = [
        'type'  => 'hidden',
        'slug'  => 'submit_user_profile',
        'value' => 1
    ];

    $content = usp_get_form( array(
        'nonce_name' => 'update-profile_' . $user_ID,
        'submit'     => __( 'Update profile', 'userspace' ),
        'onclick'    => 'usp_check_profile_form()? usp_submit_form(this): false;',
        'fields'     => $profileFields,
        'structure'  => get_site_option( 'usp_fields_profile_structure' )
        )
    );

    if ( usp_get_option( 'delete_user_account' ) ) {
        $content .= '
		<form method="post" action="" name="delete_account">
		' . wp_nonce_field( 'delete-user-' . $user_ID, '_wpnonce', true, false )
            . usp_get_button( array(
                'label'   => __( 'Delete your profile', 'userspace' ),
                'id'      => 'delete_acc',
                'icon'    => 'fa-eraser',
                'onclick' => 'return confirm("' . __( 'Are you sure? It can’t be restaured!', 'userspace' ) . '")? usp_submit_form(this): false;'
            ) )
            . '<input type="hidden" value="1" name="usp_delete_user_account"/>
		</form>';
    }

    return $content;
}

add_action( 'init', 'usp_delete_user_account_activate' );
function usp_delete_user_account_activate() {
    if ( isset( $_POST['usp_delete_user_account'] ) ) {
        add_action( 'wp', 'usp_delete_user_account' );
    }
}

//Удаляем аккаунт пользователя
function usp_delete_user_account() {
    global $user_ID, $wpdb;
    if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'delete-user-' . $user_ID ) )
        return false;

    require_once(ABSPATH . 'wp-admin/includes/user.php' );

    $wpdb->query( $wpdb->prepare( "DELETE FROM " . USP_PREF . "user_action WHERE user ='%d'", $user_ID ) );

    $delete = wp_delete_user( $user_ID );

    if ( $delete ) {
        wp_die( __( 'We are very sorry but your account has been deleted!', 'userspace' ) );
        echo '<a href="/">' . __( 'Back to main page', 'userspace' ) . '</a>';
    } else {
        wp_die( __( 'Account deletion failed! Go back and try again.', 'userspace' ) );
    }
}
