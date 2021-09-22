<?php
require_once 'classes/class-usp-dropdown-menu.php';
require_once 'classes/class-usp-dropdown-menu-group.php';

add_action( 'usp_enqueue_scripts', 'usp_dropdown_menu_scripts', 10 );
function usp_dropdown_menu_scripts() {
	usp_enqueue_style( 'usp-dropdown-menu', USP_URL . 'modules/usp-dropdown-menu/assets/css/usp-dropdown-menu.css', false, false, true );
	usp_enqueue_script( 'usp-dropdown-menu', USP_URL . 'modules/usp-dropdown-menu/assets/js/usp-dropdown-menu.js', false, false, true );
}

function get_test_dropdown_menu() {

	/**
	 * Вертикальное меню большое
	 */
	$menu_vertical_big = new USP_Dropdown_Menu( 'vertical_big', [
		'show'        => 'on_hover',
		'style'       => 'dark',
		'open_button' => [
			'icon'  => 'fa-vertical-ellipsis',
			'label' => 'Вертикальное большое',
			'type'  => 'clear'
		]
	] );

	$menu_vertical_big
		->add_button( [
			'icon'  => 'fa-star',
			'label' => 'Пункт меню 1 Пункт меню 1'
		] )
		->add_button( [
			'icon'  => 'fa-volume-off',
			'label' => 'Пункт меню 2',
		] )
		->add_button( [
			'icon'  => 'fa-expand-arrows',
			'label' => 'Пункт меню 3',
		] );


	$menu_vertical_big
		->add_group( 'primary', [ 'align_content' => 'horizontal', 'order' => 3, 'title' => 'Мини кнопки' ] )
		->add_button( [
			'icon'  => 'fa-trash',
			'label' => '',
			'size'  => 'small'
		] )
		->add_button( [
			'icon'  => 'fa-star',
			'label' => '',
			'size'  => 'small'
		] )
		->add_button( [
			'icon'  => 'fa-star',
			'label' => '',
			'size'  => 'small'
		] )
		->add_button( [
			'icon'  => 'fa-star',
			'label' => '',
			'size'  => 'small'
		] );

	$menu_vertical_big
		->add_group( 'title2', [ 'order' => 9, 'title' => 'Основные кнопки' ] );

	$menu_vertical_big
		->add_group( 'sub_menu_group', [ 'order' => 4, 'title' => 'Sub menu кнопки' ] );

	$sub_menu = new USP_Dropdown_Menu( 'sub_menu',
		[ 'position' => 'right-bottom', 'show' => 'on_hover' ] );

	$sub_menu->add_button( [
		'icon'  => 'fa-expand-arrows',
		'label' => 'Sub Пункт меню 1',
	] );
	$sub_menu->add_button( [
		'icon'  => 'fa-expand-arrows',
		'label' => 'Sub Пункт меню 2',
	] );
	$sub_menu->add_button( [
		'icon'  => 'fa-expand-arrows',
		'label' => 'Sub Пункт меню 3',
	] );
	$sub_menu->add_button( [
		'icon'  => 'fa-expand-arrows',
		'label' => 'Sub Пункт меню 4 Sub Пункт меню 4',
	] );

	$sub_menu2 = new USP_Dropdown_Menu( 'sub_menu2',
		[ 'position' => 'right-bottom', 'show' => 'on_hover' ] );

	$sub_menu2->add_button( [
		'icon'  => 'fa-expand-arrows',
		'label' => 'Sub Пункт меню 1',
	] );
	$sub_menu2->add_button( [
		'icon'  => 'fa-expand-arrows',
		'label' => 'Sub Пункт меню 2',
	] );
	$sub_menu2->add_button( [
		'icon'  => 'fa-expand-arrows',
		'label' => 'Sub Пункт меню 3',
	] );
	$sub_menu2->add_button( [
		'icon'  => 'fa-expand-arrows',
		'label' => 'Sub Пункт меню 4 Sub Пункт меню 4',
	] );

	$sub_menu->add_submenu( $sub_menu2 );

	$menu_vertical_big->get_group( 'sub_menu_group' )->add_submenu( $sub_menu );
	$menu_vertical_big->get_group( 'sub_menu_group' )->add_submenu( $sub_menu );


	$menu_vertical_big->style = 'dark';
	$sub_menu->style          = 'dark';
	$dark                     = $menu_vertical_big->get_content();


	return $dark;
}