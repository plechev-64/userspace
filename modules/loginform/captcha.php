<?php

function usp_get_simple_captcha( $args = false ) {

	if ( ! class_exists( 'ReallySimpleCaptcha' ) ) {
		return false;
	}

	$captcha = new ReallySimpleCaptcha();

	$captcha->font_size   = ( isset( $args['font_size'] ) ) ? $args['font_size'] : '16';
	$captcha->char_length = ( isset( $args['char_length'] ) ) ? $args['char_length'] : '4';
	$captcha->img_size    = ( isset( $args['img_size'] ) && is_array( $args['img_size'] ) ) ? $args['img_size'] : array(
		'72',
		'24'
	);

	$captcha->chars           = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
	$captcha->fg              = array( '0', '0', '0' );
	$captcha->bg              = array( '255', '255', '255' );
	$captcha->font_char_width = '15';
	$captcha->img_type        = 'png';
	$captcha->base            = array( '6', '18' );

	$usp_captcha_word       = $captcha->generate_random_word();
	$usp_captcha_prefix     = mt_rand();
	$usp_captcha_image_name = $captcha->generate_image( $usp_captcha_prefix, $usp_captcha_word );
	$usp_captcha_image_url  = plugins_url( 'really-simple-captcha/tmp/' );
	$usp_captcha_image_src  = $usp_captcha_image_url . $usp_captcha_image_name;

	$result = array(
		'img_size'    => $captcha->img_size,
		'char_length' => $captcha->char_length,
		'img_src'     => $usp_captcha_image_src,
		'prefix'      => $usp_captcha_prefix
	);

	return ( object ) $result;
}

function usp_captcha_check_correct( $code, $prefix ) {

	if ( ! class_exists( 'ReallySimpleCaptcha' ) ) {
		return true;
	}

	$usp_captcha = new ReallySimpleCaptcha();

	$usp_captcha_prefix = sanitize_text_field( $prefix );
	$usp_captcha_code   = sanitize_text_field( $code );

	$usp_captcha_correct = false;

	$usp_captcha_correct = $usp_captcha->check( $usp_captcha_prefix, $usp_captcha_code );

	$usp_captcha->remove( $usp_captcha_prefix );
	$usp_captcha->cleanup();

	return $usp_captcha_correct;
}

add_filter( 'usp_register_form_fields', 'usp_add_register_form_captcha', 999 );
function usp_add_register_form_captcha( $fields ) {

	$captcha = usp_get_simple_captcha();

	if ( ! $captcha ) {
		return $fields;
	}

	$fields[] = [
		'type'     => 'custom',
		'slug'     => 'simple-captcha',
		'title'    => __( 'Enter characters', 'userspace' ),
		'required' => 1,
		'content'  => '<img src="' . $captcha->img_src . '" alt="captcha" width="' . $captcha->img_size[0] . '" height="' . $captcha->img_size[1] . '" />
        <input id="usp_captcha_code" required name="usp_captcha_code" style="width: 160px;" size="' . $captcha->char_length . '" type="text" />
        <input id="usp_captcha_prefix" name="usp_captcha_prefix" type="hidden" value="' . $captcha->prefix . '" />'
	];

	return $fields;
}

add_action( 'usp_registration_errors', 'usp_check_register_captcha' );
function usp_check_register_captcha( $errors ) {

	$usp_captcha_correct = usp_captcha_check_correct( $_POST['usp_captcha_code'], $_POST['usp_captcha_prefix'] );

	if ( ! $usp_captcha_correct ) {
		$errors = new WP_Error();
		$errors->add( 'usp_register_captcha', __( 'Incorrect CAPTCHA!', 'userspace' ) );
	}

	return $errors;
}
