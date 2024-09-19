<?php
require_once 'classes/class-usp-dropdown-menu.php';
require_once 'classes/class-usp-dropdown-menu-group.php';

add_action( 'usp_enqueue_scripts', 'usp_dropdown_menu_scripts', 10 );
function usp_dropdown_menu_scripts() {
	usp_enqueue_style( 'usp-dropdown-menu', USP_URL . 'src/Module/usp-dropdown-menu/assets/css/usp-dropdown-menu.css', false, false, true );
	usp_enqueue_script( 'usp-dropdown-menu', USP_URL . 'src/Module/usp-dropdown-menu/assets/js/usp-dropdown-menu.js', false, false, true );
}

add_action( 'usp_bar_left_icons', 'usp_add_wp_menu_in_usp_bar' );
function usp_add_wp_menu_in_usp_bar() {

	$wp_usp_menu_slug = 'usp-bar';
	$locations        = get_nav_menu_locations();

	if ( empty( $locations[ $wp_usp_menu_slug ] ) ) {
		return;
	}

	$wp_usp_menu = wp_get_nav_menu_items( $locations[ $wp_usp_menu_slug ] );

	if ( ! $wp_usp_menu ) {
		return;
	}

	$menu_items = [];

	foreach ( $wp_usp_menu as $item ) {

		$menu_items[] = [
			'id'     => $item->ID,
			'url'    => $item->url,
			'title'  => $item->title,
			'parent' => $item->menu_item_parent
		];

	}

	$menu_tree = usp_menu_build_tree_recursive( $menu_items );

	$usp_menu = new USP_Dropdown_Menu( 'wp-usp-bar-menu', [
		'open_button' => [
			'label' => 'WordPress Menu',
			'icon'  => 'fa-vertical-ellipsis'
		],
		'show'        => 'on_hover',
		'style'       => 'none'
	] );

	usp_menu_build_from_tree_recursive( $menu_tree, $usp_menu );

	//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $usp_menu->get_content();

}

function usp_menu_build_tree_recursive( $items, $parent_id = 0 ) {

	$menu = [];

	foreach ( $items as $item ) {
		if ( $item['parent'] == $parent_id ) {
			$menu[] = [
				'id'       => $item['id'],
				'url'      => $item['url'],
				'title'    => $item['title'],
				'children' => usp_menu_build_tree_recursive( $items, $item['id'] )
			];
		}
	}

	return $menu;
}

function usp_menu_build_from_tree_recursive( $menu_tree, USP_Dropdown_Menu $usp_menu ) {

	foreach ( $menu_tree as $item ) {

		if ( $item['children'] ) {
			$child_menu = new USP_Dropdown_Menu( 'wp-usp-bar-menu-' . $item['id'], [
				'open_button' => [
					'label' => $item['title'],
					'icon'  => 'fa-vertical-ellipsis'
				],
				'show'        => 'on_hover',
				'position'    => 'right-bottom',
				'style'       => $usp_menu->style
			] );

			$usp_menu->add_submenu( $child_menu );

			usp_menu_build_from_tree_recursive( $item['children'], $child_menu );
		} else {

			$usp_menu->add_button( [ 'label' => $item['title'], 'href' => $item['url'] ] );

		}

	}

}