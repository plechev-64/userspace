<?php
require_once 'classes/class-usp-dropdown-menu.php';
require_once 'classes/class-usp-dropdown-menu-group.php';

add_action( 'usp_enqueue_scripts', 'usp_dropdown_menu_scripts', 10 );
function usp_dropdown_menu_scripts() {
	usp_enqueue_style( 'usp-dropdown-menu', USP_URL . 'modules/usp-dropdown-menu/assets/css/usp-dropdown-menu.css', false, false, true );
	usp_enqueue_script( 'usp-dropdown-menu', USP_URL . 'modules/usp-dropdown-menu/assets/js/usp-dropdown-menu.js', false, false, true );
}