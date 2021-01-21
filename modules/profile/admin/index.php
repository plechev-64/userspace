<?php

require_once 'class-usp-profile-fields-manager.php';
require_once 'profile-settings.php';

add_action( 'admin_menu', 'usp_profile_admin_menu', 30 );
function usp_profile_admin_menu() {
    add_submenu_page( 'manage-userspace', __( 'The form of profile', 'userspace' ), __( 'The form of profile', 'userspace' ), 'manage_options', 'manage-userfield', 'usp_profile_fields_manager' );
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

function usp_profile_fields_manager() {

    $Manager = new USP_Profile_Fields_Manager();

    $content = '<h2>' . __( 'Manage profile fields', 'userspace' ) . '</h2>';

    $content .= '<p>' . __( 'On this page you can create custom fields of the user profile, as well as to manage already created fields', 'userspace' ) . '</p>';

    $content .= $Manager->get_manager();

    echo $content;
}

//Сохраняем изменения в произвольных полях профиля со страницы пользователя
add_action( 'personal_options_update', 'usp_save_profile_fields' );
add_action( 'edit_user_profile_update', 'usp_save_profile_fields' );
function usp_save_profile_fields( $user_id ) {

    if ( ! current_user_can( 'edit_user', $user_id ) )
        return false;

    usp_update_profile_fields( $user_id );
}

//Выводим произвольные поля профиля на странице пользователя в админке
if ( is_admin() ):
    add_action( 'profile_personal_options', 'usp_get_custom_fields_profile' );
    add_action( 'edit_user_profile', 'usp_get_custom_fields_profile' );
endif;
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

    if ( $fields ) {

        USP()->use_module( 'fields' );

        $content = '<h3>' . __( 'Custom Profile Fields', 'userspace' ) . ':</h3>
        <table class="form-table usp-form usp-custom-fields-box">';

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

            $content .= '<tr class="usp-custom-field">';
            $content .= '<th><label>' . $fieldObject->get_title() . ':</label></th>';
            $content .= '<td>' . $fieldObject->get_field_input() . '</td>';
            $content .= '</tr>';
        }

        $content .= '</table>';

        foreach ( $hiddens as $field ) {

            if ( ! isset( $field['value'] ) )
                $field['value'] = get_the_author_meta( $field['slug'], $user->ID );

            $content .= USP_Field::setup( $field )->get_field_input();
        }

        echo $content;
    }
}

//save users page option in global array of options
add_action( 'usp_fields_update', 'usp_update_users_page_option', 10, 2 );
function usp_update_users_page_option( $fields, $manager_id ) {
    if ( $manager_id != 'profile' || ! isset( $_POST['users_page_usp'] ) )
        return false;
    usp_update_option( 'users_page_usp', $_POST['users_page_usp'] );
}

//add users page value in the time of saving global options of plugin
add_filter( 'usp_global_options_pre_update', 'usp_add_options_users_page_value', 10 );
function usp_add_options_users_page_value( $values ) {
    $values['users_page_usp'] = usp_get_option( 'users_page_usp', 0 );
    return $values;
}
