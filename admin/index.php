<?php

require_once "admin-menu.php";

add_action( 'current_screen', 'usp_admin_init' );
function usp_admin_init( $current_screen ) {
	if ( preg_match( '/(userspace_page|manage-userspace|profile|user-edit)/', $current_screen->base ) ) {

		usp_admin_resources();

		USP()->use_module( 'forms' );
	}

	if ( $current_screen->base == 'userspace_page_usp-tabs-manager' || $current_screen->base == 'userspace_page_usp-register-form-manager' ) {
		USP()->use_module( 'fields-manager' );
	}
}

add_filter( 'display_post_states', 'usp_mark_own_page', 10, 2 );
function usp_mark_own_page( $post_states, $post ) {

	if ( $post->post_type === 'page' ) {

		$plugin_pages = get_site_option( 'usp_plugin_pages' );

		if ( ! $plugin_pages ) {
			return $post_states;
		}

		if ( in_array( $post->ID, $plugin_pages ) ) {
			$post_states[] = __( 'The page of plugin UserSpace', 'userspace' );
		}
	}

	return $post_states;
}

// set admin area root inline css colors
add_filter( 'admin_head', 'usp_admin_css_variable' );
function usp_admin_css_variable() {
	echo '<style>' . usp_get_root_colors() . '</style>';
}

// get standart header of admin
function usp_get_admin_header( $title = false, $subtitle = false ) {
	$out = '<div class="usp-admin-head usps usps__jc-between">';

	$out .= '<div class="usp-admin-head__left usps usps__column">';

	$out .= '<div class="usp-admin-head__top usps">';
	$out .= '<div class="usp-admin-head__logo usps usps__ai-center"><span class="dashicons dashicons-palmtree"></span><span class="usp-admin-head__name">UserSpace</span></div>';
	$out .= '<div class="usp-admin-head__version">v.' . USP_VERSION . '</div>';
	$out .= '</div>';

	$out .= '<div class="usp-admin-head__bottom usps usps__column">';
	$out .= '<h2 class="usp-admin-head__title">' . $title . '</h2>';
	$out .= '<div class="usp-admin-head__subtitle usps">' . $subtitle . '</div>';
	$out .= '</div>';

	$out .= '</div>'; // end .usp-admin-head__left

	$out .= '<div class="usp-admin-head__right usps usps__grow usps__jc-end">';
	$out .= '<div class="usps usps__ai-center"><span class="dashicons dashicons-media-document"></span><a href="#" target="_blank">' . __( 'Documentation', 'userspace' ) . '</a></div>';
	$out .= '<div class="usps usps__ai-center"><span class="dashicons dashicons-editor-help"></span><a href="#" target="_blank">' . __( 'Support', 'userspace' ) . '</a></div>';
	$out .= '</div>'; // end .usp-admin-head__right

	$out .= '</div>'; // end .usp-admin-head

	return $out;
}

// get standart content of admin
function usp_get_admin_content( $content, $no_sidebar = false ) {
	$class = ( $no_sidebar ) ? 'usp-admin__fullwidth' : '';

	$out = '<div class="usp-admin__box usps usps__nowrap usps__jc-between">';
	$out .= '<div class="usp-admin__settings usps__grow ' . $class . '">' . $content . '</div>';
	if ( ! $no_sidebar ) {
		/**
		 * On the plugin settings pages, adds custom html to the sidebar.
		 *
		 * @param string    added custom html.
		 *
		 * @since 1.0
		 *
		 */
		$out .= '<div class="usp-admin__sidebar usps usps__column usps__ai-end usps__grow">' . apply_filters( 'usp_admin_sidebar', '' ) . '</div>';
	}

	$out .= '</div>';

	return $out;
}

add_filter( 'usp_admin_sidebar', 'usp_admin_sidebar_about_notice', 10 );
function usp_admin_sidebar_about_notice( $content ) {
	// get plugin description header
	$text = get_file_data( USP_PATH . 'userspace.php', [ 'description' => 'Description' ] );

	$content .= usp_get_notice( [
		'text'   => 'UserSpace - ' . $text['description'],
		'type'   => 'simple',
		'icon'   => false,
		'cookie' => 'usp_userspace_about',
	] );

	return $content;
}

add_filter( 'usp_admin_sidebar', 'usp_admin_sidebar_find_addons_notice', 11 );
function usp_admin_sidebar_find_addons_notice( $content ) {
	$text = sprintf( __( 'Plugins that extend UserSpace can be found in the WordPress  %srepository%s.', 'userspace' ), '<a href="https://wordpress.org/plugins/tags/userspace/" target="_blank">', '</a>' );

	$content .= usp_get_notice( [ 'text' => $text, 'type' => 'simple', 'icon' => false ] );

	return $content;
}

add_filter( 'usp_admin_sidebar', 'usp_admin_sidebar_rate_me_notice', 12 );
function usp_admin_sidebar_rate_me_notice( $content ) {
	$text = sprintf( __( 'If you liked plugin %sUserSpace%s, please vote for it in repository %s★★★★★%s. Thank you so much!', 'userspace' ), '<strong>', '</strong>', '<a href="" target="_blank">', '</a>' );

	$content .= usp_get_notice( [
		'text'   => $text,
		'type'   => 'simple',
		'icon'   => false,
		'cookie' => 'usp_repo_votes',
	] );

	return $content;
}
