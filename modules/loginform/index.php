<?php

require_once 'registration.php';
require_once 'authorization.php';

if ( $GLOBALS['pagenow'] === 'wp-login.php' ) {
	wp_enqueue_style( 'usp-wploginform', USP_URL . 'modules/loginform/assets/css/usp-loginform-wp-login.css' );
	wp_enqueue_style( 'usp-core', USP_URL . 'assets/css/usp-core.css' );

	require_once 'wp-register-form.php';
}

if ( class_exists( 'ReallySimpleCaptcha' ) ) {
	require_once 'captcha.php';
}

if ( usp_is_ajax() ) {
	usp_loginform_assets();
} else if ( is_admin() ) {
	add_action( 'admin_enqueue_scripts', 'usp_loginform_assets', 10 );
} else {
	add_action( 'usp_enqueue_scripts', 'usp_loginform_assets', 10 );
}
function usp_loginform_assets() {
	if ( ! usp_get_option( 'usp_login_form' ) ) {
		usp_dialog_scripts();
	}

	usp_enqueue_style( 'usp-entryform', USP_URL . 'modules/loginform/assets/css/usp-loginform.css', false, false, true );
	usp_enqueue_script( 'usp-entryform', USP_URL . 'modules/loginform/assets/js/usp-loginform.js', false, false, true );
}

function usp_get_loginform( $atts = [] ) {
	$atr = shortcode_atts( [
		'active' => 'login',
		'forms'  => 'login,register,lostpassword'
	], $atts );

	$forms = array_map( 'trim', explode( ',', $atr['forms'] ) );

	$content = '<div class="usp-entry-form usps__relative">';

	$content .= '<div class="usp-entry-tabs usps usps__line-1">';
	if ( in_array( 'login', $forms ) ) {
		$content .= usp_get_button( [
			'type'      => 'clear',
			'href'      => '#login',
			'class'     => 'usp-entry-tab usp-entry-tab__login',
			'status'    => ( $atr['active'] == 'login' ) ? 'active' : '',
			'onclick'   => 'USP.loginform.tabShow("login",this);return false;',
			'label'     => __( 'Login', 'userspace' ),
			'size'      => 'medium',
			'fullwidth' => '1',
		] );
	}

	if ( in_array( 'register', $forms ) ) {
		$content .= usp_get_button( [
			'type'      => 'clear',
			'href'      => '#register',
			'class'     => 'usp-entry-tab usp-entry-tab__register',
			'status'    => ( $atr['active'] == 'register' ) ? 'active' : '',
			'onclick'   => 'USP.loginform.tabShow("register",this);return false;',
			'label'     => __( 'Registration', 'userspace' ),
			'size'      => 'medium',
			'fullwidth' => '1',
		] );
	}

	$content .= '</div>';

	$content .= apply_filters( 'usp_loginform_notice', '' );

	if ( in_array( 'login', $forms ) ) {

		$content .= '<div class="usp-entry-box usp-entry-box__login' . ( $atr['active'] == 'login' ? ' usp-entry-box__active' : '' ) . '">';

		$content .= usp_get_form( [
			'submit'  => __( 'Login', 'userspace' ),
			'onclick' => 'USP.loginform.send("login",this);return false;',
			'fields'  => apply_filters( 'usp_login_form_fields', [
				[
					'slug'        => 'user_login',
					'type'        => 'text',
					'title'       => __( 'Login or E-mail', 'userspace' ),
					'placeholder' => __( 'Login or E-mail', 'userspace' ),
					'icon'        => 'fa-user',
					'maxlenght'   => 50,
					'required'    => 1
				],
				[
					'slug'        => 'user_pass',
					'type'        => 'password',
					'title'       => __( 'Password', 'userspace' ),
					'placeholder' => __( 'Password', 'userspace' ),
					'icon'        => 'fa-key',
					'maxlenght'   => 50,
					'required'    => 1
				]
			] )
		] );

		if ( in_array( 'lostpassword', $forms ) ) {
			$content .= usp_get_button( [
				'type'    => 'clear',
				'href'    => '#',
				'class'   => 'usp-forgot',
				'onclick' => 'USP.loginform.tabShow("lostpassword",this);return false;',
				'label'   => __( 'Forgot password?', 'userspace' ),
				'size'    => 'medium',
			] );
		}

		$content .= '</div>';
	}

	if ( in_array( 'register', $forms ) ) {

		$content .= '<div class="usp-entry-box usp-entry-box__register' . ( $atr['active'] == 'register' ? ' usp-entry-box__active' : '' ) . '">';
		$content .= usp_get_form( [
				'submit'    => __( 'Registration', 'userspace' ),
				'onclick'   => 'USP.loginform.send("register",this);return false;',
				'fields'    => usp_get_register_form_fields(),
				'structure' => get_site_option( 'usp_fields_register_form_structure' )
			]
		);
		$content .= '</div>';
	}

	if ( in_array( 'lostpassword', $forms ) ) {
		$content .= '<div class="usp-entry-box usp-entry-box__lostpassword' . ( $atr['active'] == 'lostpassword' ? ' usp-entry-box__active' : '' ) . '">';
		$content .= usp_get_form( [
				'submit'  => __( 'Get a new password', 'userspace' ),
				'onclick' => 'USP.loginform.send("lostpassword",this);return false;',
				'fields'  => apply_filters( 'usp_lostpassword_form_fields', [
						[
							'type'        => 'text',
							'slug'        => 'user_login',
							'title'       => __( 'Login', 'userspace' ),
							'placeholder' => __( 'Login or E-mail', 'userspace' ),
							'icon'        => 'fa-user',
							'maxlenght'   => 50,
							'required'    => 1
						]
					]
				)
			]
		);
		$content .= '</div>';
	}

	$content .= '</div>';

	return $content;
}

function usp_get_loginform_url( $type ) {

	if ( $type == 'login' ) {
		switch ( usp_get_option( 'usp_login_form' ) ) {
			case 1:
				return add_query_arg( [ 'usp-form' => 'login' ], get_permalink( usp_get_option( 'usp_id_login_page' ) ) );
				break;
			case 2:
				return wp_login_url( get_permalink( usp_get_option( 'usp_id_login_page' ) ) );
				break;
			default:
				return '#';
				break;
		}
	}

	if ( $type == 'register' ) {
		switch ( usp_get_option( 'usp_login_form' ) ) {
			case 1:
				return add_query_arg( [ 'usp-form' => 'register' ], get_permalink( usp_get_option( 'usp_id_login_page' ) ) );
				break;
			case 2:
				return wp_registration_url();
				break;
			default:
				return '#';
				break;
		}
	}
}

// call the login and registration form
usp_ajax_action( 'usp_call_loginform', true );
function usp_call_loginform() {
	global $user_ID;

	$form = $_POST['form'];

	if ( $user_ID ) {
		return [
			'error' => __( 'You are already logged in!', 'userspace' )
		];
	}

	return [
		'dialog' => [
			'size'        => 'auto',
			'class'       => 'usp-entry-modal ssi-no-padding ssi-dialog',
			'buttonClose' => false,
			'content'     => usp_get_loginform( [ 'active' => $form ] )
		]
	];
}

// we accept data sent from the login and registration form
usp_ajax_action( 'usp_send_loginform', true );
function usp_send_loginform() {

	$tab_id     = $_POST['tab_id'];
	$user_login = isset( $_POST['user_login'] ) ? sanitize_user( $_POST['user_login'] ) : false;

	if ( $tab_id == 'login' ) {

		$password = sanitize_text_field( $_POST['user_pass'] );

		$user = wp_signon( [
			'user_login'    => $user_login,
			'user_password' => $password,
			'remember'      => isset( $_POST['remember'] ) ? true : false,
		] );

		if ( is_wp_error( $user ) ) {
			return [
				'error' => $user->get_error_message()
			];
		}

		usp_user_update_activity();

		return [
			'redirect' => usp_get_authorize_url( $user->ID ),
			'success'  => __( 'Successful authorization', 'userspace' )
		];
	} else if ( $tab_id == 'register' ) {

		$user_email = sanitize_email( $_POST['user_email'] );

		if ( ! $user_login ) {
			$user_login = $user_email;
		}

		$user_id = register_new_user( $user_login, $user_email );

		if ( is_wp_error( $user_id ) ) {
			return [
				'error' => $user_id->get_error_message()
			];
		}

		return [
			'content' => usp_get_notice( [
				'type' => 'success',
				'text' => __( 'Registration is complete, check your email, '
				              . 'then go to the <a href="#" onclick="USP.loginform.tabShow(\'login\',this); return false;">login page</a>', 'userspace' )
			] ),
			'success' => __( 'Successful registration', 'userspace' )
		];
	} else if ( $tab_id == 'lostpassword' ) {

		$result = retrieve_password();

		if ( is_wp_error( $result ) ) {
			return [
				'error' => $result->get_error_message()
			];
		}

		return [
			'content' => usp_get_notice( [
				'type' => 'success',
				'text' => __( 'A link to reset your password has been sent to your email', 'userspace' )
			] ),
			'success' => __( 'Email sent successfully', 'userspace' )
		];
	}
}

add_filter( 'usp_loginform_notice', 'usp_add_login_form_notice', 10 );
function usp_add_login_form_notice( $notice ) {

	if ( ! isset( $_REQUEST['formaction'] ) || ! $_REQUEST['formaction'] ) {
		return $notice;
	}

	switch ( $_REQUEST['formaction'] ) {
		case 'success-checkemail':
			$notice = usp_get_notice( [
				'success' => __( 'Your email has been successfully confirmed! Log in using your username and password', 'userspace' )
			] );
			break;
	}

	return $notice;
}

add_filter( 'usp_login_form_fields', 'usp_add_login_form_custom_data', 10 );
function usp_add_login_form_custom_data( $fields ) {

	ob_start();

	do_action( 'login_form' );

	$content = ob_get_contents();
	ob_end_clean();

	if ( ! $content ) {
		return $fields;
	}

	$fields[] = [
		'slug'    => 'custom_data',
		'type'    => 'custom',
		'content' => $content
	];

	return $fields;
}

add_filter( 'usp_login_form_fields', 'usp_add_rememberme_button', 20 );
function usp_add_rememberme_button( $fields ) {

	$fields[] = [
		'slug'   => 'rememberme',
		'type'   => 'checkbox',
		'icon'   => 'fa-key',
		'values' => [
			1 => __( 'Remember me', 'userspace' )
		]
	];

	return $fields;
}

if ( $GLOBALS['pagenow'] !== 'wp-login.php' ) {
	add_filter( 'usp_register_form_fields', 'usp_add_register_form_custom_data', 10 );
}
function usp_add_register_form_custom_data( $fields ) {

	ob_start();

	do_action( 'register_form' );

	$content = ob_get_contents();
	ob_end_clean();

	if ( ! $content ) {
		return $fields;
	}

	$fields[] = [
		'slug'    => 'custom_data',
		'type'    => 'custom',
		'content' => $content
	];

	return $fields;
}

add_filter( 'usp_lostpassword_form_fields', 'usp_add_lostpassword_form_custom_data', 10 );
function usp_add_lostpassword_form_custom_data( $fields ) {

	ob_start();

	do_action( 'lostpassword_form' );

	$content = ob_get_contents();
	ob_end_clean();

	if ( ! $content ) {
		return $fields;
	}

	$fields[] = [
		'slug'    => 'custom_data',
		'type'    => 'custom',
		'content' => $content
	];

	return $fields;
}
