<?php

/**
 * Registration of a new tab in personal account.
 *
 * @param   $tab_data   array   Data new tab:
 * <pre>
 * $tab_data['id']          string  Tab ID (slug).
 * $tab_data['name']        string  Tab name (text on button).
 * $tab_data['icon']        string  Tab icon.
 *                                  Default: 'fa-cog'
 * $tab_data['counter']     int     Tab counter.
 * $tab_data['hidden']      bool    The tab will be hidden in the list of tabs, but it will be accessed via a direct link.
 * $tab_data['supports']    array   Additional features.
 *                                  Available: ['ajax','cache','dialog']
 *                                  ajax - ajax tab load.
 *                                  cache - file caching.
 *                                  dialog - open content in dialog modal.
 * $tab_data['public']      int     Tab Privacy.
 *                                  Default: 0 - output to all.
 *                                  Available: 0, -1, -2
 * $tab_data['output']      string  Area output.
 *                                  Default: menu
 *                                  Available: menu, counters, actions
 * $tab_data['content']     array   Child tab data.
 * $tab_data['content']['callback']['name'] callable    Callback function.
 * $tab_data['content']['callback']['args'] array       Additional vars. Example: array( $arg_1, $arg_2 )
 * </pre>
 *
 * @return  false|void
 *
 * @see     USP_Tab
 *
 * @since   1.0.0
 */
function usp_tab( $tab_data ) {
	/**
	 * The filter allows you to access the tab data.
	 *
	 * @param   $tab_data   array   Tab data.
	 *
	 * @since   1.0.0
	 */
	$tab_data = apply_filters( 'usp_tab', $tab_data );

	if ( ! $tab_data ) {
		return false;
	}

	USP()->tabs()->add( $tab_data );
}

/**
 * Registration of a child tab in personal account.
 *
 * @param   $tab_id     string  Parent tab ID.
 * @param   $subtabData array   Children tab data:
 * <pre>
 * $subtabData['id']    string  Tab ID (slug).
 * $subtabData['name']  string  Tab name (text on button).
 * $subtabData['icon']  string  Tab icon.
 *                              Default: 'fa-cog'
 * $tab_data['counter'] int     Tab counter.
 * $tab_data['callback']['name']    callable    Callback function.
 * $tab_data['callback']['args']    array       Additional vars. Example: array( $arg_1, $arg_2 )
 * </pre>
 *
 * @return false|void
 *
 * @see     USP_Sub_Tab
 *
 * @since   1.0.0
 */
function usp_add_sub_tab( $tab_id, $subtabData ) {
	$tab = USP()->tabs()->tab( $tab_id );
	if ( ! $tab ) {
		return false;
	}

	$tab->add_subtab( $subtabData );
}

/**
 * Get all tabs
 *
 * @return  array
 *
 * @see     USP_Tabs
 *
 * @since   1.0.0
 */
function usp_get_tabs() {
	return USP()->tabs()->get_tabs();
}

/**
 * Get tab by ID (by slug)
 *
 * @param   $tab_id string  Tab ID (slug)
 *
 * @return  false|mixed
 *
 * @see     USP_Tab
 *
 * @since   1.0.0
 */
function usp_get_tab( $tab_id ) {
	return USP()->tabs()->tab( $tab_id );
}

/**
 * Get subtab tab by tab & subtab ID (slug)
 *
 * @param   $tab_id     string  Tab ID (slug)
 * @param   $subtab_id  string  Subtab ID (slug)
 *
 * @return  false|mixed
 *
 * @see     USP_Tab
 *
 * @since   1.0.0
 */
function usp_get_subtab( $tab_id, $subtab_id ) {
	$tab = usp_get_tab( $tab_id );

	if ( ! $tab ) {
		return false;
	}

	$subtab = $tab->subtab( $subtab_id );

	return $subtab ?: false;
}

/**
 * Gets a link to the personal account by the user ID.
 * It is also possible to get a link to a tab (by ID), or to a child tab.
 *
 * @param   $user_id    int     ID user.
 * @param   $tab_id     string  Optional. Slug tab.
 * @param   $subtab_id  string  Optional. Slug subtab.
 *
 * @return  string  New URL query string (unescaped).
 *
 * @since   1.0.0
 */
function usp_get_tab_permalink( $user_id, $tab_id = false, $subtab_id = false ) {
	/*
	 * todo удалить, заменить использования на метод в USP_User
	 */
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
	/**
	 * Filters tab content.
	 *
	 * @param   $content    string  Tab content.
	 *
	 * @since   1.0.0
	 */
	return apply_filters( 'usp_custom_tab_content', stripslashes_deep( $content ) );
}

add_filter( 'usp_custom_tab_content', 'usp_filter_custom_tab_vars', 6 );
function usp_filter_custom_tab_vars( $content ) {
	$data = [
		'{USERID}'   => get_current_user_id(),
		'{MASTERID}' => USP()->office()->get_owner_id()
	];
	/**
	 * Filters tab variables.
	 * Like as {USERID} or {MASTERID}
	 *
	 * @param   $data array   Tab vars.
	 *
	 * @since   1.0.0
	 */
	$var_data = apply_filters( 'usp_custom_tab_vars', $data );

	if ( ! $var_data ) {
		return $content;
	}

	return strtr( $content, $var_data );
}

add_filter( 'usp_custom_tab_content', 'usp_filter_custom_tab_user_meta', 5 );
function usp_filter_custom_tab_user_meta( $content ) {
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

	$matches = [];

	foreach ( $metas[1] as $meta ) {
		// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
		if ( in_array( $meta, $tblUsers ) ) {
			$value = get_the_author_meta( $meta, USP()->office()->get_owner_id() );
		} else {
			$value = get_user_meta( USP()->office()->get_owner_id(), $meta, 1 );
		}

		if ( ! $value ) {
			$value = __( 'Not selected', 'userspace' );
		}

		$matches[ '{USP-UM:' . $meta . '}' ] = ( is_array( $value ) ) ? implode( ', ', $value ) : $value;
	}

	return strtr( $content, $matches );
}

add_filter( 'usp_tab_content', 'usp_check_user_blocked', 10 );
function usp_check_user_blocked( $content ) {
	if ( ! is_user_logged_in() ) {
		return $content;
	}

	if ( USP()->office()->is_owner( get_current_user_id() ) ) {
		return $content;
	}

	if ( get_user_meta( USP()->office()->get_owner_id(), 'usp_black_list:' . get_current_user_id() ) ) {
		$content = usp_get_notice( [ 'type' => 'error', 'text' => __( 'This user has restricted your access to their page.', 'userspace' ) ] );
	}

	return $content;
}

add_action( 'usp_init_tabs', 'usp_add_block_black_list_button', 10 );
function usp_add_block_black_list_button() {
	if ( ! is_user_logged_in() ) {
		return;
	}

	if ( USP()->office()->is_owner( get_current_user_id() ) ) {
		return;
	}

	$user_block = get_user_meta( get_current_user_id(), 'usp_black_list:' . USP()->office()->get_owner_id() );

	$title = ( $user_block ) ? __( 'Unblock', 'userspace' ) : __( 'Block', 'userspace' );

	usp_tab(
		[
			'id'      => 'blacklist',
			'name'    => $title,
			'public'  => - 2,
			'output'  => 'actions',
			'icon'    => 'fa-user',
			'onclick' => 'usp_manage_user_black_list(this, ' . USP()->office()->get_owner_id() . ', "' . __( 'Are you sure?', 'userspace' ) . '");return false;'
		]
	);
}
