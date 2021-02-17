<?php

USP()->use_module( 'fields-manager' );

class USP_Profile_Fields_Manager extends USP_Fields_Manager {
    function __construct() {
        global $wpdb;

        parent::__construct( 'profile', array(
            'option_name'    => 'usp_profile_fields',
            'empty_field'    => 0,
            'structure_edit' => 1,
            'meta_delete'    => array(
                $wpdb->usermeta => 'meta_key'
            ),
            'default_fields' => apply_filters( 'usp_default_profile_fields', array(
                array(
                    'slug'  => 'first_name',
                    'title' => __( 'Firstname', 'userspace' ),
                    'icon'  => 'fa-user',
                    'type'  => 'text'
                ),
                array(
                    'slug'  => 'last_name',
                    'title' => __( 'Surname', 'userspace' ),
                    'icon'  => 'fa-user',
                    'type'  => 'text'
                ),
                array(
                    'slug'  => 'display_name',
                    'title' => __( 'Name to be displayed', 'userspace' ),
                    'icon'  => 'fa-user',
                    'type'  => 'text'
                ),
                array(
                    'slug'  => 'user_url',
                    'title' => __( 'Website', 'userspace' ),
                    'icon'  => 'fa-link',
                    'type'  => 'url'
                ),
                array(
                    'slug'  => 'description',
                    'title' => __( 'Status', 'userspace' ),
                    'icon'  => 'fa-comment',
                    'type'  => 'textarea'
                ),
                array(
                    'slug'  => 'usp_birthday',
                    'title' => __( 'Birthday', 'userspace' ),
                    'icon'  => 'fa-birthday-cake',
                    'type'  => 'date'
                ),
                array(
                    'slug'        => 'usp_sex',
                    'title'       => __( 'Sex', 'userspace' ),
                    'icon'        => 'fa-user',
                    'type'        => 'radio',
                    'values'      => [ __( 'Man', 'userspace' ), __( 'Woman', 'userspace' ) ],
                    'empty_first' => __( 'Not selected', 'userspace' ),
                ),
                array(
                    'slug'     => 'user_email',
                    'title'    => __( 'E-mail', 'userspace' ),
                    'type'     => 'email',
                    'required' => 1
                ),
                array(
                    'slug'     => 'primary_pass',
                    'title'    => __( 'New password', 'userspace' ),
                    'type'     => 'password',
                    'required' => 0,
                    'notice'   => __( 'If you want to change your password - enter a new one', 'userspace' )
                ),
                array(
                    'slug'     => 'repeat_pass',
                    'title'    => __( 'Repeat password', 'userspace' ),
                    'type'     => 'password',
                    'required' => 0,
                    'notice'   => __( 'Repeat the new password', 'userspace' )
                ) )
            ),
            'field_options'  => apply_filters( 'usp_profile_field_options', array(
                array(
                    'slug'  => 'notice',
                    'type'  => 'textarea',
                    'title' => __( 'Field description', 'userspace' )
                ),
                array(
                    'slug'   => 'required',
                    'type'   => 'radio',
                    'title'  => __( 'Required field', 'userspace' ),
                    'values' => array( __( 'No', 'userspace' ), __( 'Yes', 'userspace' ) )
                ),
                array(
                    'slug'   => 'public_value',
                    'type'   => 'radio',
                    'title'  => __( 'Show the content to other users', 'userspace' ),
                    'values' => array( __( 'No', 'userspace' ), __( 'Yes', 'userspace' ) )
                ),
                array(
                    'slug'   => 'admin',
                    'type'   => 'radio',
                    'title'  => __( 'Can be changed only by the site administration', 'userspace' ),
                    'values' => array( __( 'No', 'userspace' ), __( 'Yes', 'userspace' ) )
                ),
                array(
                    'slug'   => 'filter',
                    'type'   => 'radio',
                    'title'  => __( 'Filter users by this field', 'userspace' ),
                    'values' => array( __( 'No', 'userspace' ), __( 'Yes', 'userspace' ) )
                )
            ) )
        ) );

        $this->setup_default_fields();
    }

    function get_manager_options_form_fields() {

        $fields = array(
            'users_page_usp' => array(
                'type'    => 'custom',
                'title'   => __( 'Users page', 'userspace' ),
                'notice'  => __( 'This page is required to filter users by value of profile fields', 'userspace' ),
                'content' => wp_dropdown_pages( array(
                    'selected'         => usp_get_option( 'users_page_usp' ),
                    'name'             => 'users_page_usp',
                    'show_option_none' => __( 'Not selected', 'userspace' ),
                    'echo'             => 0
                    )
                )
            ) );

        return $fields;
    }

}
