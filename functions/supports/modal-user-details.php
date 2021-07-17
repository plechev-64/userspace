<?php

add_action( 'usp_enqueue_scripts', 'usp_support_user_info_scripts', 10 );
function usp_support_user_info_scripts() {
	if ( ! usp_is_office() ) {
		return;
	}

	usp_dialog_scripts();
	usp_enqueue_script( 'usp-user-info-js', USP_URL . 'functions/supports/assets/js/usp-user-details.js', false, true );
}

add_action( 'usp_enqueue_scripts', 'usp_support_user_info_style', 10 );
function usp_support_user_info_style() {
	if ( ! usp_is_office() ) {
		return;
	}

	usp_enqueue_style( 'usp-user-info-css', USP_URL . 'functions/supports/assets/css/usp-user-details.css' );
}

add_filter( 'usp_init_js_variables', 'usp_init_js_user_info_variables', 10 );
function usp_init_js_user_info_variables( $data ) {
	if ( usp_is_office() ) {
		$data['local']['title_user_info'] = __( 'Detailed information', 'userspace' );
	}

	return $data;
}

add_filter( 'usp_avatar_bttns', 'usp_add_user_info_button', 10 );
function usp_add_user_info_button( $buttons ) {
	usp_dialog_scripts();

	$args    = [
		'type'    => 'simple',
		'size'    => 'medium',
		'class'   => 'usp-ava__info usp-ava__bttn usps__jc-center',
		'title'   => __( 'User info', 'userspace' ),
		'onclick' => 'usp_get_user_info(this);return false;',
		'href'    => '#',
		'icon'    => 'fa-info-circle',
	];
	$buttons .= usp_get_button( $args );

	return $buttons;
}

usp_ajax_action( 'usp_return_user_details', true );
function usp_return_user_details() {
	return [
		'content' => usp_get_user_details( intval( $_POST['user_id'] ) )
	];
}

function usp_get_user_details( $user_id, $set_args = false ) {

	$defaults = [
		'zoom'          => true,
		'description'   => true,
		'custom_fields' => true
	];

	$args = wp_parse_args( $set_args, $defaults );

	$content = '<div class="usp-user-avatar usps__relative">';
	$content .= usp_get_avatar( $user_id, 300, false, [ 'class' => 'usp-detailed-ava' ] );

	if ( $args['zoom'] ) {
		$avatar = get_user_meta( $user_id, 'usp_avatar', 1 );

		if ( $avatar ) {
			$url_avatar = get_avatar_url( $user_id, [ 'size' => 1000 ] );
			$content    .= '<a title="' . __( 'Zoom avatar', 'userspace' ) . '" data-zoom="' . $url_avatar . '" onclick="usp_zoom_avatar(this);return false;" class="usp-avatar-zoom usps__hidden" href="#"><i class="uspi fa-search-plus usps usps__column usps__jc-center usps__grow"></i></a>';
		}
	}

	$content .= '</div>';

	if ( $args['description'] ) {
		$content .= usp_get_quote_box( $user_id, [ 'side' => 'top' ] );
	}

	if ( $args['custom_fields'] ) {
		$content .= usp_show_user_custom_fields( $user_id );
	}

	return '<div id="usp-user-details" class="usps usps__nowrap usps__column">' . $content . '</div>';
}
