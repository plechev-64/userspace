<?php

add_action( 'admin_menu', 'usp_admin_menu', 19 );
function usp_admin_menu() {
    add_menu_page( __( 'UserSpace', 'userspace' ), __( 'UserSpace', 'userspace' ), 'manage_options', 'manage-userspace', 'usp_global_options' );
    add_submenu_page( 'manage-userspace', __( 'Settings', 'userspace' ), __( 'Settings', 'userspace' ), 'manage_options', 'manage-userspace', 'usp_global_options' );
    add_submenu_page( 'manage-userspace', __( 'Tabs manager', 'userspace' ), __( 'Tabs manager', 'userspace' ), 'manage_options', 'usp-tabs-manager', 'usp_admin_tabs_manager' );
    add_submenu_page( 'manage-userspace', __( 'Registration form', 'userspace' ), __( 'Registration form', 'userspace' ), 'manage_options', 'usp-register-form-manager', 'usp_register_form_manager' );
}

function usp_register_form_manager() {

    require_once 'classes/class-usp-register-form-manager.php';

    $Manager = new USP_Register_Form_Manager( );

    $content = '<h2>' . __( 'Editing registration form fields', 'userspace' ) . '</h2>';

    $content .= $Manager->get_manager();

    echo $content;
}

// Plugin settings in the admin panel
function usp_global_options() {
    require_once 'pages/options.php';
}

function usp_admin_tabs_manager() {
    require_once 'pages/tabs-manager.php';
}
