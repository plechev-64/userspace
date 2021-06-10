<?php

require_once 'class-usp-profile-fields-manager.php';
require_once 'profile-settings.php';

// Profile page in WordPress admin
add_action( 'admin_enqueue_scripts', 'usp_admin_profile_style' );
function usp_admin_profile_style( $page ) {
    if ( $page == 'user-edit.php' || $page == 'profile.php' ) {
        usp_enqueue_style( 'usp-admin-profile', USP_URL . 'modules/profile/admin/assets/css/usp-admin-profile.css', false, false, true );
    }
}

add_action( 'admin_menu', 'usp_profile_admin_menu', 30 );
function usp_profile_admin_menu() {
    add_submenu_page( 'manage-userspace', __( 'The form of profile', 'userspace' ), __( 'The form of profile', 'userspace' ), 'manage_options', 'manage-userfield', 'usp_profile_fields_manager' );
}

function usp_profile_fields_manager() {

    $Manager = new USP_Profile_Fields_Manager();

    $title = __( 'Manage profile fields', 'userspace' );

    $subtitle = __( 'On this page you can create custom fields of the user profile, as well as to manage already created fields', 'userspace' );

    $header = usp_get_admin_header( $title, $subtitle );

    $content = usp_get_admin_content( $Manager->get_manager(), 'no_sidebar' );

    echo $header . $content;
}

add_filter( 'usp_field_options', 'usp_setup_profile_manager_field_options', 10, 3 );
function usp_setup_profile_manager_field_options( $options, $field, $manager_id ) {

    if ( ! $field->id || $manager_id != 'profile' )
        return $options;

    $defaultFields = array(
        'first_name',
        'last_name',
        'display_name',
        'url',
        'description'
    );

    if ( in_array( $field->id, $defaultFields ) ) {
        unset( $options['filter'] );
        unset( $options['public_value'] );
    } else if ( in_array( $field->type, array( 'editor', 'uploader', 'file' ) ) ) {
        unset( $options['filter'] );
    }

    if ( in_array( $field->type, [ 'uploader', 'file' ] ) ) {
        unset( $options['required'] );
    }

    return $options;
}

// Save changes in custom profile fields from the user's page
add_action( 'personal_options_update', 'usp_save_profile_fields' );
add_action( 'edit_user_profile_update', 'usp_save_profile_fields' );
function usp_save_profile_fields( $user_id ) {
    if ( ! current_user_can( 'edit_user', $user_id ) )
        return;

    usp_update_profile_fields( $user_id );
}

// Output custom profile fields on the user's page in the admin panel
if ( is_admin() ) {
    add_action( 'profile_personal_options', 'usp_get_custom_fields_profile' );
    add_action( 'edit_user_profile', 'usp_get_custom_fields_profile' );
}
function usp_get_custom_fields_profile( $user ) {

    $args = array(
        'exclude' => array(
            'first_name',
            'last_name',
            'description',
            'user_url',
            'display_name',
            'user_email',
            'primary_pass',
            'repeat_pass',
            'show_admin_bar_front'
        ),
        'user_id' => $user->ID
    );

    $fields = apply_filters( 'usp_admin_profile_fields', usp_get_profile_fields( $args ), $user );

    if ( ! $fields )
        return;

    USP()->use_module( 'fields' );

    $content = '<h2>' . __( 'Custom Profile Fields', 'userspace' ) . '</h2>';
    $content .= '<div class="usp-admin-profile usp-form preloader-parent">';
    $content .= '<div class="usp-content">';
    $content .= '<div class="usp-content-group">';

    $hiddens = array();
    foreach ( $fields as $field ) {

        if ( $field['type'] == 'hidden' ) {
            $hiddens[] = $field;
            continue;
        }

        if ( ! isset( $field['value_in_key'] ) )
            $field['value_in_key'] = true;

        if ( ! isset( $field['value'] ) )
            $field['value'] = get_the_author_meta( $field['slug'], $user->ID );

        $fieldObject = USP_Field::setup( $field );

        $content .= '<div id="usp-field-' . $field['slug'] . '-wrapper" class="usp-field type-' . $field['type'] . '-field">';
        $content .= $fieldObject->get_title();
        $content .= $fieldObject->get_field_input();
        $content .= '</div>';
    }

    $content .= '</div>';
    $content .= '</div>';
    $content .= '</div>';

    foreach ( $hiddens as $field ) {

        if ( ! isset( $field['value'] ) )
            $field['value'] = get_the_author_meta( $field['slug'], $user->ID );

        $content .= USP_Field::setup( $field )->get_field_input();
    }

    echo $content;
}
