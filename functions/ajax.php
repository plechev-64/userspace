<?php

function usp_is_ajax() {
	return ( defined( 'DOING_AJAX' ) && DOING_AJAX || isset( $GLOBALS['wp']->query_vars['rest_route'] ) );
}

function USP_Ajax() {
	return USP_Ajax::getInstance();
}

function usp_verify_ajax_nonce() {
	USP_Ajax()->verify();
}

function usp_rest_action( $function_name ) {
	USP_Ajax()->init_rest( $function_name );
}

function usp_ajax_action( $callback, $guest_access = false, $modules = true ) {
	USP_Ajax()->init_ajax_callback( $callback, $guest_access, $modules );
}

usp_rest_action( 'usp_ajax_call' );
function usp_ajax_call() {
	global $user_ID;

	USP_Ajax()->verify();

	if ( empty( $_POST['call_action'] ) ) {
		wp_send_json( [
			'error' => __( 'Unregistered callback', 'userspace' )
		] );
	}

	$callback = sanitize_text_field( wp_unslash( $_POST['call_action'] ) );
	$modules  = ! empty( $_POST['used_modules'] ) ? usp_recursive_map( 'sanitize_text_field', wp_unslash( $_POST['used_modules'] ) ) : false;

	if ( $modules ) {
		foreach ( $modules as $module_id ) {
			USP()->use_module( $module_id );
		}
	}

	$callbackProps = USP_Ajax()->get_ajax_callback( $callback );

	if ( ! $callbackProps ) {

		wp_send_json( [
			'error' => __( 'Unregistered callback', 'userspace' )
		] );
	}

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

	wp_enqueue_script( 'usp-core-scripts', USP_URL . 'assets/js/usp-core.js', [ 'jquery' ], USP_VERSION );

	$respond = $callback();

	$respond['used_modules'] = $modules ? array_unique( $modules + USP()->get_used_modules() ) : USP()->get_used_modules();

	wp_send_json( $respond );
}

usp_ajax_action( 'usp_load_tab', true, true );
function usp_load_tab() {

	$tab_id    = ! empty( $_POST['tab_id'] ) ? sanitize_text_field( wp_unslash( $_POST['tab_id'] ) ) : '';
	$subtab_id = ! empty( $_POST['subtab_id'] ) ? sanitize_text_field( wp_unslash( $_POST['subtab_id'] ) ) : '';
	$office_id = ! empty( $_POST['office_id'] ) ? intval( $_POST['office_id'] ) : 0;

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

	$content .= apply_filters( 'usp_ajax_tab_content', $tab->subtab( $subtab_id )->get_content() );

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

	if ( empty( $_POST['databeat'] ) ) {
		return [ 'error' => __( 'Error', 'userspace' ) ];
	}

	$databeat = usp_recursive_map( 'sanitize_text_field', json_decode( wp_unslash( $_POST['databeat'] ) ) );
	$result   = [ 'beat_result' => [] ];

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

/**
 * TODO refactoring blacklist functionality
 */
usp_ajax_action( 'usp_manage_user_black_list', false );
function usp_manage_user_black_list() {

	if ( empty( $_POST['user_id'] ) || ! get_userdata( $_POST['user_id'] ) ) {
		return [
			'error' => __( 'Error', 'userspace' )
		];
	}

	$user_id = absint( $_POST['user_id'] );

	$user_block = get_user_meta( get_current_user_id(), 'usp_black_list:' . $user_id );

	if ( $user_block ) {
		delete_user_meta( get_current_user_id(), 'usp_black_list:' . $user_id );
		do_action( 'remove_user_blacklist', $user_id );
	} else {
		add_user_meta( get_current_user_id(), 'usp_black_list:' . $user_id, 1 );
		do_action( 'add_user_blacklist', $user_id );
	}

	$new_status = $user_block ? 0 : 1;

	return [
		'label' => ( $new_status ) ? __( 'Unblock', 'userspace' ) : __( 'Block', 'userspace' )
	];
}

usp_ajax_action( 'usp_get_emoji_ajax', false );
function usp_get_emoji_ajax() {
	global $wpsmiliestrans;

	$content = [];

	$smilies = [];
	foreach ( $wpsmiliestrans as $emo => $smilie ) {
		$smilies[ $smilie ] = $emo;
	}

	foreach ( $smilies as $smilie => $emo ) {
		if ( ! $emo ) {
			continue;
		}
		$content[] = str_replace( 'style="height: 1em; max-height: 1em;"', '', convert_smilies( $emo ) );
	}

	if ( ! $content ) {
		return [
			'error' => __( 'Failed to load emoticons', 'userspace' )
		];
	}

	return [
		'content' => implode( '', $content )
	];
}

/* new uploader */
usp_ajax_action( 'usp_upload', true );
function usp_upload() {

	if ( empty( $_POST['options'] ) ) {
		return [
			'error' => __( 'Error', 'userspace' )
		];
	}

	$options = usp_recursive_map( 'sanitize_text_field', json_decode( wp_unslash( $_POST['options'] ) ) );

	if ( empty( $options['class_name'] ) ) {
		return [
			'error' => __( 'Error', 'userspace' )
		];
	}

	$class_name = $options['class_name'];

	if ( $class_name == 'USP_Uploader' ) {
		$uploader = new $class_name( $options['uploader_id'], $options );
	} else if ( is_subclass_of( $class_name, 'USP_Uploader' ) ) {
		$uploader = new $class_name( $options );
	} else {
		return [
			'error' => __( 'Error', 'userspace' )
		];
	}

	if ( md5( json_encode( $uploader ) . usp_get_option( 'usp_security_key' ) ) != $_POST['sk'] ) {
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

	if ( empty( $_POST['attach_id'] ) ) {
		return array(
			'error' => __( 'The data has been wrong!', 'userspace' )
		);
	}

	$attachment_id = intval( $_POST['attach_id'] );
	$post_id       = ! empty( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
	$access_error  = [ 'error' => __( 'You can`t delete this file!', 'userspace' ) ];

	if ( $post_id ) {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $access_error;
		}
	}


	if ( ! is_user_logged_in() ) {

		$media = ( new USP_Temp_Media() )->where( [ 'media_id' => $attachment_id ] )->get_row();

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
