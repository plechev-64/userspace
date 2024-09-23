<?php


/**
 * This function will return true if performing a wp ajax call.
 *
 * @return bool
 *
 * @since   1.0.0
 */
function usp_is_ajax() {
	return ( defined( 'DOING_AJAX' ) && DOING_AJAX || isset( $GLOBALS['wp']->query_vars['rest_route'] ) );
}

// phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
function Ajax() {
	return Ajax::getInstance();
}

/**
 * Check nonce field in usp_ajax_action() function.
 * Can be used instead of wp_verify_nonce
 *
 * @return  void    false - if nonce incorrect. String wp_send_json() error message or die();
 *
 * @since   1.0.0
 */
function usp_verify_ajax_nonce() {
	Ajax()->verify();
}

function usp_rest_action( $function_name ) {
	Ajax()->init_rest( $function_name );
}

/**
 * Calls the callback functions that have been added to the usp_ajax() data action method.
 *
 * @param   $callback       string  Callback function from js usp_ajax data action method.
 * @param   $guest_access   bool    If guest access is needed.
 *                                  Default: false
 * @param   $modules        bool
 *
 * @since   1.0.0
 */
function usp_ajax_action( $callback, $guest_access = false, $modules = true ) {
	Ajax()->init_ajax_callback( $callback, $guest_access, $modules );
}

usp_rest_action( 'usp_ajax_call' );
function usp_ajax_call() {
	Ajax()->verify();

	// phpcs:disable WordPress.Security.NonceVerification.Missing
	if ( empty( $_POST['call_action'] ) ) {
		wp_send_json( [
			'error' => __( 'Unregistered callback', 'userspace' )
		] );
	}

	$callback = sanitize_text_field( wp_unslash( $_POST['call_action'] ) );

	$callbackProps = Ajax()->get_ajax_callback( $callback );

	if ( ! $callbackProps ) {
		wp_send_json( [
			'error' => __( 'Unregistered callback', 'userspace' )
		] );
	}

	global $user_ID;

	if ( ! $user_ID && ! $callbackProps['guest'] ) {
		wp_send_json( [
			'error' => __( 'Access to callback is forbidden', 'userspace' )
		] );
	}

	if ( ! function_exists( $callback ) ) {
		wp_send_json( [
			'error' => __( 'Function is not found', 'userspace' )
		] );
	}

	// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter
	wp_enqueue_script( 'usp-core-scripts', USP_URL . 'assets/js/usp-core.js', [ 'jquery' ], USP_VERSION );

	$respond = $callback();

	wp_send_json( $respond );
}

usp_ajax_action( 'usp_load_tab', true );
function usp_load_tab() {
	usp_verify_ajax_nonce();

	// phpcs:disable WordPress.Security.NonceVerification.Missing
	$tab_id    = ! empty( $_POST['tab_id'] ) ? sanitize_text_field( wp_unslash( $_POST['tab_id'] ) ) : '';
	$subtab_id = ! empty( $_POST['subtab_id'] ) ? sanitize_text_field( wp_unslash( $_POST['subtab_id'] ) ) : '';
	$office_id = ! empty( $_POST['office_id'] ) ? intval( $_POST['office_id'] ) : 0;
	// phpcs:enable WordPress.Security.NonceVerification.Missing

	$tab = USP()->tabs()->tab( $tab_id );

	if ( ! $tab ) {
		return [ 'error' => __( 'Data of the requested tab was not found.', 'userspace' ) ];
	}

	$ajax = ( in_array( 'ajax', $tab->supports ) || in_array( 'dialog', $tab->supports ) ) ? 1 : 0;

	if ( ! $ajax ) {
		return [ 'error' => __( 'Perhaps this add-on does not support ajax loading', 'userspace' ) ];
	}

	USP()->office()->set_owner( $office_id );

	USP()->tabs()->current_id = $tab_id;
	$tab->current_id          = $subtab_id ?: $tab->content[0]->id;

	$content = $tab->get_menu();

	/**
	 * Filters the contents of the tab.
	 *
	 * @param   $content    string  Tab content.
	 * @param   $tab        object  Tab Object.
	 * @param   $office_id  int     ID of the personal account.
	 *
	 * @see     Tab
	 *
	 * @since       1.0.0
	 *
	 */
	$content .= apply_filters( 'usp_ajax_tab_content', $tab->subtab( $subtab_id )->get_content(), $tab, $office_id );

	return [
		'content'   => $content,
		'tab'       => $tab,
		'tab_id'    => $tab->id,
		'subtab_id' => $subtab_id ?: '',
		'tab_url'   => $tab->subtab( $subtab_id )->get_permalink(),
		'supports'  => $tab->supports
	];
}

// process the heartbeat of the plugin
usp_ajax_action( 'usp_beat', true );
function usp_beat() {
	usp_verify_ajax_nonce();

	// phpcs:disable WordPress.Security.NonceVerification.Missing
	if ( empty( $_POST['databeat'] ) ) {
		return [ 'error' => __( 'Error', 'userspace' ) ];
	}

	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$databeat = usp_recursive_map( 'sanitize_text_field', json_decode( wp_unslash( $_POST['databeat'] ) ) );
	// phpcs:enable WordPress.Security.NonceVerification.Missing

	$result = [ 'beat_result' => [] ];

	if ( $databeat ) {
		foreach ( $databeat as $data ) {
			if ( ! usp_beat_action_exist( $data->beat_name, $data->action ) ) {
				continue;
			}

			$callback = $data->action;

			$result['beat_result'][] = [
				'result'    => $callback( $data->data ),
				'success'   => $data->success,
				'beat_name' => $data->beat_name
			];
		}
	}

	return $result;
}


usp_ajax_action( 'usp_manage_user_black_list' );
function usp_manage_user_black_list() {
	usp_verify_ajax_nonce();

	// phpcs:disable WordPress.Security.NonceVerification.Missing
	if ( empty( $_POST['user_id'] ) || ! get_userdata( intval( wp_unslash( $_POST['user_id'] ) ) ) ) {
		return [
			'error' => __( 'Error', 'userspace' )
		];
	}

	$user_id = absint( $_POST['user_id'] );
	// phpcs:enable WordPress.Security.NonceVerification.Missing

	$usp_user   = USP()->user( get_current_user_id() );
	$is_blocked = $usp_user->is_blocked( $user_id );
	if ( $is_blocked ) {
		$usp_user->unblock( $user_id );
	} else {
		$usp_user->block( $user_id );
	}

	$new_status = $is_blocked ? 0 : 1;

	return [
		'label' => ( $new_status ) ? __( 'Unblock', 'userspace' ) : __( 'Block', 'userspace' )
	];
}

usp_ajax_action( 'usp_get_emoji_ajax' );
function usp_get_emoji_ajax() {
	usp_verify_ajax_nonce();

	global $wpsmiliestrans;

	$content = [];
	$emojis  = [];

	foreach ( $wpsmiliestrans as $emo => $smile ) {
		$emojis[ $smile ] = $emo;
	}

	foreach ( $emojis as $k => $emo ) {
		if ( ! $emo ) {
			continue;
		}
		$content[] = str_replace( 'style="height: 1em; max-height: 1em;"', '', convert_smilies( $emo ) );
	}

	if ( ! $content ) {
		return [
			'error' => __( 'Failed to load emojis', 'userspace' )
		];
	}

	return [
		'content' => implode( '', $content )
	];
}

/* new uploader */
usp_ajax_action( 'usp_upload', true );
function usp_upload() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing
	if ( empty( $_POST['options'] ) ) {
		return [
			'error' => __( 'Error', 'userspace' )
		];
	}

	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$options = ( array ) json_decode( wp_unslash( $_POST['options'] ) );

	if ( empty( $options['class_name'] ) ) {
		return [
			'error' => __( 'Error', 'userspace' )
		];
	}

	$class_name = $options['class_name'];

	if ( 'Uploader' == $class_name ) {
		$uploader = new $class_name( $options['uploader_id'], $options );
	} else if ( is_subclass_of( $class_name, 'Uploader' ) ) {
		$uploader = new $class_name( $options );
	} else {
		return [
			'error' => __( 'Error', 'userspace' )
		];
	}
	$secret = isset( $_POST['sk'] ) ? sanitize_text_field( wp_unslash( $_POST['sk'] ) ) : false;
	// phpcs:enable WordPress.Security.NonceVerification.Missing
	$secret_check = md5( wp_json_encode( $uploader ) . usp_get_option( 'usp_security_key' ) );

	if ( ! $secret || $secret_check != $secret ) {
		return [
			'error' => __( 'Error of security', 'userspace' )
		];
	}

	$files = $uploader->upload();

	if ( $files ) {
		return $files;
	} else {
		return [
			'error' => __( 'Something has been wrong', 'userspace' )
		];
	}
}

// deleting photos attached to a post via the plugin loader
usp_ajax_action( 'usp_ajax_delete_attachment', true );
function usp_ajax_delete_attachment() {
	usp_verify_ajax_nonce();

	// phpcs:disable WordPress.Security.NonceVerification.Missing
	if ( empty( $_POST['attach_id'] ) ) {
		return [
			'error' => __( 'The data has been wrong!', 'userspace' )
		];
	}

	$attachment_id = intval( $_POST['attach_id'] );
	$post_id       = ! empty( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
	$access_error  = [ 'error' => __( 'You can`t delete this file!', 'userspace' ) ];
	// phpcs:enable WordPress.Security.NonceVerification.Missing

	if ( $post_id ) {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $access_error;
		}
	}

	if ( ! is_user_logged_in() ) {
		$media = ( new TempMediaQuery() )->where( [ 'media_id' => $attachment_id ] )->get_row();

		$session_id = ! empty( $_COOKIE['PHPSESSID'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['PHPSESSID'] ) ) : 'none';

		if ( $media->session_id != $session_id ) {
			return $access_error;
		}
	} else {
		if ( ! current_user_can( 'edit_post', $attachment_id ) ) {
			return $access_error;
		}
	}

	usp_delete_temp_media( $attachment_id );

	wp_delete_attachment( $attachment_id, true );

	return [
		'success' => __( 'The file has been successfully deleted!', 'userspace' )
	];
}
