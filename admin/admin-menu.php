<?php

add_action( 'admin_menu', 'usp_admin_menu', 19 );
function usp_admin_menu() {
    add_menu_page( __( 'WP-RECALL', 'usp' ), __( 'WP-RECALL', 'usp' ), 'manage_options', 'manage-wprecall', 'usp_dashboard' );
    add_submenu_page( 'manage-wprecall', __( 'SETTINGS', 'usp' ), __( 'SETTINGS', 'usp' ), 'manage_options', 'usp-options', 'usp_global_options' );
    add_submenu_page( 'manage-wprecall', __( 'Tabs manager', 'usp' ), __( 'Tabs manager', 'usp' ), 'manage_options', 'usp-tabs-manager', 'usp_admin_tabs_manager' );
    add_submenu_page( 'manage-wprecall', __( 'Форма регистрации', 'usp' ), __( 'Форма регистрации', 'usp' ), 'manage_options', 'usp-register-form-manager', 'usp_register_form_manager' );
}

function usp_register_form_manager() {

    require_once 'classes/class-usp-register-form-manager.php';

    $Manager = new USP_Register_Form_Manager( );

    $content = '<h2>' . __( 'Управление поля формы регистрации', 'usp' ) . '</h2>';

    $content .= $Manager->get_manager();

    echo $content;
}

//Настройки плагина в админке
function usp_global_options() {
    require_once 'pages/options.php';
}

function usp_admin_tabs_manager() {
    require_once 'pages/tabs-manager.php';
}

function usp_dashboard() {
    echo 'coming soon';
}
