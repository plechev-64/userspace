<?php
require_once 'classes/DropdownMenu.php';
require_once 'classes/DropdownMenuGroup.php';

add_action( 'usp_enqueue_scripts', 'usp_dropdown_menu_scripts', 10 );
function usp_dropdown_menu_scripts() {
	usp_enqueue_style( 'usp-dropdown-menu', USP_URL . 'src/Module/usp-dropdown-menu/assets/css/usp-dropdown-menu.css', false, false, true );
	usp_enqueue_script( 'usp-dropdown-menu', USP_URL . 'src/Module/usp-dropdown-menu/assets/js/usp-dropdown-menu.js', false, false, true );
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

function usp_menu_build_from_tree_recursive( $menu_tree, DropdownMenu $usp_menu ) {

	foreach ( $menu_tree as $item ) {

		if ( $item['children'] ) {
			$child_menu = new DropdownMenu( 'wp-usp-bar-menu-' . $item['id'], [
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