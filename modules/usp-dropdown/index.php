<?php
require_once 'classes/class-usp-dropdown-new.php';
require_once 'classes/class-usp-dropdown-group.php';

add_action( 'usp_enqueue_scripts', 'usp_dropdown_new_scripts', 10 );
function usp_dropdown_new_scripts() {
	usp_enqueue_style( 'usp-dropdown-new', USP_URL . 'modules/usp-dropdown/assets/css/usp-dropdown.css', false, false, true );
	//usp_enqueue_script( 'usp-dropdown-new', USP_URL . 'modules/usp-dropdown/assets/js/usp-dropdown.js', false, false, true );
}

function get_test_dropdown_menu() {

	/**
	 * Вертикальное стандарт
	 */
	$menu_vertical = new USP_Dropdown_New( 'vertical', [
		'icon'  => 'fa-vertical-ellipsis',
		'label' => 'Вертикальное',
		'size' => 'medium',
		'type' => 'simple',
	]);

	$menu_vertical
		->add_button( [
			'icon'  => 'fa-star',
			'size'  => 'medium',
			'type'  => 'simple',
			'label' => 'Пункт меню 1'
		] )
		->add_button( [
			'icon'  => 'fa-star',
			'size'  => 'medium',
			'type'  => 'simple',
			'label' => 'Пункт меню 2'
		] )
		->add_button( [
			'icon'  => 'fa-star',
			'size'  => 'medium',
			'type'  => 'simple',
			'label' => 'Пункт меню 3'
		] )
		->add_button( [
			'icon'  => 'fa-star',
			'size'  => 'medium',
			'type'  => 'simple',
			'label' => 'Пункт меню 4'
		] );

	/**
	 * Вертикальное меню большое
	 */
	$menu_vertical_big = new USP_Dropdown_New( 'vertical_big', [
		'icon'  => 'fa-vertical-ellipsis',
		'label' => 'Вертикальное большое',
		'size' => 'medium',
		'type' => 'simple',
	] );

	$menu_vertical_big
		->add_button( [
			'size'  => 'medium',
			'type'  => 'simple',
			'icon'  => 'fa-star',
			'label' => 'Пункт меню 1'
		] )
		->add_button( [
			'size'  => 'medium',
			'type'  => 'simple',
			'icon'  => 'fa-volume-off',
			'label' => 'Пункт меню 2',
		] )
		->add_button( [
			'size'  => 'medium',
			'type'  => 'simple',
			'icon'  => 'fa-expand-arrows',
			'label' => 'Пункт меню 3',
		] );

	$menu_vertical_big
		->add_group( 'title1', [ 'order' => 1 ] )
		->add_item( '<div style="padding: 12px; text-align: center"><b>Мини кнопки</b></div>' );

	$menu_vertical_big
		->add_group( 'primary', [ 'align_content' => 'horizontal', 'order' => 3 ] )
		->add_button( [
			'size' => 'medium',
			'type' => 'simple',
			'icon' => 'fa-trash'
		] )
		->add_button( [
			'size' => 'medium',
			'type' => 'simple',
			'icon' => 'fa-star'
		] );

	$menu_vertical_big
		->add_group( 'title2', [ 'order' => 3 ] )
		->add_item( '<div style="padding: 12px; text-align: center"><b>Основные кнопки</b></div>' );

	/**
	 * Меню только с 1ой группой по горизонтали
	 */

	$menu_horizontal = new USP_Dropdown_New( 'horizontal_menu', [
		'icon'  => 'fa-vertical-ellipsis',
		'label' => 'Горизонтальное меню',
		'size' => 'medium',
		'type' => 'simple',
	] );

	$sub_menu_horizontal = new USP_Dropdown_New( 'horizontal_menu', [
		'icon' => 'fa-star',
		'size' => 'medium',
		'type' => 'simple',
	] );

	$sub_menu_horizontal->add_button( [
		'fa-trash',
		'size'  => 'medium',
		'type'  => 'simple',
		'label' => 'Саб меню'
	] );
	$sub_menu_horizontal->add_button( [
		'fa-trash',
		'size'  => 'medium',
		'type'  => 'simple',
		'label' => 'Саб меню'
	] );
	$sub_menu_horizontal->add_button( [
		'fa-trash',
		'size'  => 'medium',
		'type'  => 'simple',
		'label' => 'Саб меню'
	] );
	$sub_menu_horizontal->add_button( [
		'fa-trash',
		'size'  => 'medium',
		'type'  => 'simple',
		'label' => 'Саб меню'
	] );

	$menu_horizontal
		->add_group( 'primary', [ 'align_content' => 'horizontal' ] )
		->add_item( $sub_menu_horizontal->get_content() )
		->add_item( $sub_menu_horizontal->get_content() )
		->add_item( $sub_menu_horizontal->get_content() )
		->add_item( $sub_menu_horizontal->get_content() );

	return $menu_vertical->get_content() . $menu_vertical_big->get_content() . $menu_horizontal->get_content();
}