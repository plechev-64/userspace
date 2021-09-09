<?php
require_once 'classes/class-usp-dropdown-new.php';
require_once 'classes/class-usp-dropdown-group.php';

add_action( 'usp_enqueue_scripts', 'usp_dropdown_new_scripts', 10 );
function usp_dropdown_new_scripts() {
	usp_enqueue_style( 'usp-dropdown-new', USP_URL . 'modules/usp-dropdown/assets/css/usp-dropdown.css', false, false, true );
	usp_enqueue_script( 'usp-dropdown-new', USP_URL . 'modules/usp-dropdown/assets/js/usp-dropdown.js', false, false, true );
}

function get_test_dropdown_menu() {

	/**
	 * Вертикальное меню большое
	 */
	$menu_vertical_big = new USP_Dropdown_New( 'vertical_big', [
		'icon'  => 'fa-vertical-ellipsis',
		'label' => 'Вертикальное большое',
		'size'  => 'medium',
		'type'  => 'simple',
	] );

	$menu_vertical_big
		->add_button( [
			'size'  => 'medium',
			'type'  => 'clear',
			'icon'  => 'fa-star',
			'label' => 'Пункт меню 1'
		] )
		->add_button( [
			'size'  => 'medium',
			'type'  => 'clear',
			'icon'  => 'fa-volume-off',
			'label' => 'Пункт меню 2',
		] )
		->add_button( [
			'size'  => 'medium',
			'type'  => 'clear',
			'icon'  => 'fa-expand-arrows',
			'label' => 'Пункт меню 3',
		] );

	$menu_vertical_big
		->add_group( 'title1', [ 'order' => 1 ] )
		->add_item( '<div style="padding: 12px; text-align: center"><b>Мини кнопки</b></div>' );

	$menu_vertical_big
		->add_group( 'primary', [ 'align_content' => 'horizontal', 'order' => 3 ] )
		->add_button( [
			'size'  => 'small',
			'type'  => 'clear',
			'icon'  => 'fa-trash',
			'label' => ''
		] )
		->add_button( [
			'size'  => 'small',
			'type'  => 'clear',
			'icon'  => 'fa-star',
			'label' => ''
		] )
		->add_button( [
			'size'  => 'small',
			'type'  => 'clear',
			'icon'  => 'fa-star',
			'label' => ''
		] )
		->add_button( [
			'size'  => 'small',
			'type'  => 'clear',
			'icon'  => 'fa-star',
			'label' => ''
		] );

	$menu_vertical_big
		->add_group( 'title2', [ 'order' => 3 ] )
		->add_item( '<div style="padding: 12px; text-align: center"><b>Основные кнопки</b></div>' );

	$menu_vertical_big
		->add_group( 'title3', [ 'order' => 4 ] )
		->add_item( '<div style="padding: 12px; text-align: center"><b>Sub menu кнопки</b></div>' );


	$sub_menu = new USP_Dropdown_New( 'sub_menu', [
		'icon'  => 'fa-vertical-ellipsis',
		'label' => 'Еще меню',
		'size'  => 'medium',
		'type'  => 'simple',
	],
	['position' => 'right']);

	$sub_menu->add_button( [
		'size'  => 'medium',
		'type'  => 'clear',
		'icon'  => 'fa-expand-arrows',
		'label' => 'Sub Пункт меню 1',
	] );
	$sub_menu->add_button( [
		'size'  => 'medium',
		'type'  => 'clear',
		'icon'  => 'fa-expand-arrows',
		'label' => 'Sub Пункт меню 2',
	] );
	$sub_menu->add_button( [
		'size'  => 'medium',
		'type'  => 'clear',
		'icon'  => 'fa-expand-arrows',
		'label' => 'Sub Пункт меню 3',
	] );
	$sub_menu->add_button( [
		'size'  => 'medium',
		'type'  => 'clear',
		'icon'  => 'fa-expand-arrows',
		'label' => 'Sub Пункт меню 4 Sub Пункт меню 4',
	] );


	$menu_vertical_big
		->add_group( 'sub_menu', [ 'order' => 5 ] )
		->add_item( $sub_menu->get_content() );

	$menu_vertical_big
		->add_group( 'sub_menu2', [ 'order' => 5 ] )
		->add_item( $sub_menu->get_content() );


	return $menu_vertical_big->get_content();
}