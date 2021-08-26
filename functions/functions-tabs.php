<?php

// registering a personal account tab
function usp_tab( $tab_data ) {

	$tab_data = apply_filters( 'usp_tab', $tab_data );

	if ( ! $tab_data ) {
		return false;
	}

	USP()->tabs()->add( $tab_data );
}

// registering a child tab
function usp_add_sub_tab( $tab_id, $subtabData ) {

	if ( ! $tab = USP()->tabs()->tab( $tab_id ) ) {
		return false;
	}

	$tab->add_subtab( $subtabData );
}

function usp_get_tabs() {
	return USP()->get_tabs();
}

function usp_get_tab( $tab_id ) {
	return USP()->tabs()->tab( $tab_id );
}

function usp_get_subtab( $tab_id, $subtab_id ) {

	$tab = usp_get_tab( $tab_id );

	if ( ! $tab ) {
		return false;
	}

	$subtab = $tab->subtab( $subtab_id );

	return $subtab ?: false;
}

/**
 * Gets a link to the personal account by the user ID
 *
 * @param int $user_id id user.
 * @param string $tab_id Optional. Slug tab.
 * @param string $subtab_id Optional. Slug subtab.
 *
 * @return string New URL query string (unescaped).
 * @since 1.0
 *
 */
function usp_get_tab_permalink( $user_id, $tab_id = false, $subtab_id = false ) {
	if ( ! $tab_id ) {
		return usp_user_get_url( $user_id );
	}

	return add_query_arg( [ 'tab' => $tab_id, 'subtab' => $subtab_id ], usp_user_get_url( $user_id ) );
}

/* old variation */
function usp_format_url( $url, $tab_id = false, $subtab_id = false ) {
	$ar_perm = explode( '?', $url );
	$cnt     = count( $ar_perm );
	if ( $cnt > 1 ) {
		$a = '&';
	} else {
		$a = '?';
	}
	$url = $url . $a;
	if ( $tab_id ) {
		$url .= 'tab=' . $tab_id;
	}
	if ( $subtab_id ) {
		$url .= '&subtab=' . $subtab_id;
	}

	return $url;
}

// displaying the content of an arbitrary tab
add_filter( 'usp_custom_tab_content', 'do_shortcode', 11 );
add_filter( 'usp_custom_tab_content', 'wpautop', 10 );
function usp_custom_tab_content( $content ) {
	return apply_filters( 'usp_custom_tab_content', stripslashes_deep( $content ) );
}

add_filter( 'usp_custom_tab_content', 'usp_filter_custom_tab_vars', 6 );
function usp_filter_custom_tab_vars( $content ) {
	global $user_ID;

	$matchs = array(
		'{USERID}'   => $user_ID,
		'{MASTERID}' => USP()->office()->get_owner_id()
	);

	$matchs = apply_filters( 'usp_custom_tab_vars', $matchs );

	if ( ! $matchs ) {
		return $content;
	}

	return strtr( $content, $matchs );
}

add_filter( 'usp_custom_tab_content', 'usp_filter_custom_tab_usermetas', 5 );
function usp_filter_custom_tab_usermetas( $content ) {
	global $usp_office;

	preg_match_all( '/{USP-UM:([^}]+)}/', $content, $metas );

	if ( ! $metas[1] ) {
		return $content;
	}

	$tblUsers = [
		'display_name',
		'user_url',
		'user_login',
		'user_nicename',
		'user_email',
		'user_registered'
	];

	$matchs = array();

	foreach ( $metas[1] as $meta ) {

		if ( in_array( $meta, $tblUsers ) ) {
			$value = get_the_author_meta( $meta, $usp_office );
		} else {
			$value = get_user_meta( $usp_office, $meta, 1 );
		}

		if ( ! $value ) {
			$value = __( 'Not selected', 'userspace' );
		}

		$matchs[ '{USP-UM:' . $meta . '}' ] = ( is_array( $value ) ) ? implode( ', ', $value ) : $value;
	}

	return strtr( $content, $matchs );
}

add_filter( 'usp_tab_content', 'usp_check_user_blocked', 10 );
function usp_check_user_blocked( $content ) {
	global $user_ID;
	/*
	 * TODO не понятно
	 */
	if ( USP()->office()->is_owner( $user_ID ) ) {
		if ( get_user_meta( USP()->office()->is_owner( $user_ID ), 'usp_black_list:' . $user_ID ) ) {
			$content = usp_get_notice( [ 'text' => __( 'The user has restricted access to their page', 'userspace' ) ] );
		}
	}

	return $content;
}

add_action( 'usp_init_tabs', 'usp_add_block_black_list_button', 10 );
function usp_add_block_black_list_button() {
	if ( ! is_user_logged_in() ) {
		return;
	}

	$user_block = get_user_meta( get_current_user_id(), 'usp_black_list:' . USP()->office()->get_owner_id() );

	$title = ( $user_block ) ? __( 'Unblock', 'userspace' ) : __( 'Block', 'userspace' );

	usp_tab(
		array(
			'id'      => 'blacklist',
			'name'    => $title,
			'public'  => - 2,
			'output'  => 'actions',
			'icon'    => 'fa-user',
			'onclick' => 'usp_manage_user_black_list(this, ' . USP()->office()->get_owner_id() . ', "' . __( 'Are you sure?', 'userspace' ) . '");return false;'
		)
	);
}
