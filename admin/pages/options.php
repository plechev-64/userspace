<?php

global $wpdb;

usp_awesome_font_style();

wp_enqueue_script( 'jquery' );
wp_enqueue_script( 'jquery-ui-dialog' );
wp_enqueue_style( 'wp-jquery-ui-dialog' );

$usp_options = get_site_option( 'usp_global_options' );

$pages = usp_get_pages_ids();

$options = new OptionsManager( [
	'option_name'  => 'usp_global_options',
	'page_options' => 'manage-userspace',
	'extends'      => true,
] );

$options->add_box( 'primary', [
	'title' => __( 'General settings', 'userspace' ),
	'icon'  => 'fa-cogs',
] )->add_group( 'office', [
	'title'  => __( 'User profile page', 'userspace' ),
	'extend' => true,
] )->add_options( [
	[
		'type'   => 'select',
		'slug'   => 'account_page',
		'title'  => __( 'Page with shortcode for displaying user profile page', 'userspace' ),
		'values' => $pages,
	],
	[
		'type'      => 'runner',
		'slug'      => 'usp_user_timeout',
		'value_min' => 1,
		'value_max' => 20,
		'default'   => 10,
		'help'      => __( 'This value sets the maximum time a user is considered "online" in the absence of activity', 'userspace' ),
		'title'     => __( 'Current user inactivity timeout', 'userspace' ),
		'notice'    => __( 'Specify the time in minutes after which the user will be considered offline if you did not show activity on the website. The default is 10 minutes.', 'userspace' ),
	],
] );

$options->box( 'primary' )->add_group( 'design', [
	'title' => __( 'Design', 'userspace' ),
] )->add_options( [
	[
		'slug'   => 'usp_current_office',
		'type'   => 'select',
		'title'  => __( 'Select a user profile page theme', 'userspace' ),
		'values' => USP()->themes()->get_themes(),
	],
	[
		'type'       => 'uploader',
		'temp_media' => 1,
		'multiple'   => 0,
		'crop'       => 1,
		'filetitle'  => 'usp-default-avatar',
		'filename'   => 'usp-default-avatar',
		'slug'       => 'usp_default_avatar',
		'title'      => __( 'Default avatar', 'userspace' ),
	],
	[
		'type'       => 'runner',
		'value_min'  => 0,
		'value_max'  => 5120,
		'value_step' => 256,
		'default'    => 1024,
		'slug'       => 'usp_avatar_weight',
		'title'      => __( 'Max weight of avatars', 'userspace' ) . ', Kb',
		'notice'     => __( 'Set the image upload limit in kb, by default', 'userspace' ) . ' 1024Kb' .
		                '. ' . __( 'If 0 is specified, download is disallowed.', 'userspace' ),
	],
] );

$options->box( 'primary' )->add_group( 'user_sign', [
	'title' => __( 'Login and register', 'userspace' ),
] )->add_options( [
	[
		'type'      => 'select',
		'slug'      => 'usp_login_form',
		'title'     => __( 'The order of output the form of login and registration', 'userspace' ),
		'values'    => [
			__( 'Floating form', 'userspace' ),
			__( 'On a separate page', 'userspace' ),
			__( 'Wordpress Forms', 'userspace' ),
			__( 'Widget form', 'userspace' ),
		],
		'notice'    => __( 'The form of login and registration of the plugin can be displayed with help of widget "Control panel" 
		and a shortcode [usp-loginform], but you can use the standard login form of WordPress also', 'userspace' ),
		'children' => [
			1 => [
				[
					'type'   => 'select',
					'slug'   => 'usp_id_login_page',
					'title'  => __( 'ID of the shortcode page [usp-loginform]', 'userspace' ),
					'values' => $pages,
				],
			],
		],
	],
	[
		'type'    => 'switch',
		'slug'    => 'usp_confirm_register',
		'help'    => __( 'If this option is checked, newly registered users will receive a confirmation email to the email address specified during registration. This email contains a confirmation link that the user has to click, in order to activate the account.', 'userspace' ),
		'title'   => __( 'Registration requires email confirmation', 'userspace' ),
		'text'    => [
			'off' => __( 'No', 'userspace' ),
			'on'  => __( 'Yes', 'userspace' ),
		],
		'default' => 0,
	],
	[
		'type'      => 'radio',
		'slug'      => 'usp_authorize_page',
		'title'     => __( 'Redirect user after login', 'userspace' ),
		'values'    => [
			__( 'The user profile', 'userspace' ),
			__( 'Current page', 'userspace' ),
			__( 'Arbitrary URL', 'userspace' ),
		],
		'default'   => 0,
		'children' => [
			2 => [
				[
					'type'   => 'text',
					'slug'   => 'usp_custom_authorize_page',
					'title'  => __( 'URL', 'userspace' ),
					'notice' => __( 'Enter your URL below, if you select an arbitrary URL after login', 'userspace' ),
				],
			],
		],
	],
] );

$options->box( 'primary' )->add_group( 'access_console', [
	'title' => __( 'Access to the console', 'userspace' ),
] )->add_options( [
	[
		'type'   => 'checkbox',
		'slug'   => 'usp_console_access',
		'title'  => __( 'Access to the console is allowed', 'userspace' ),
		'values' => usp_get_roles_ids( [ 'administrator', 'banned', 'need-confirm' ] ),
		'notice' => __( 'The administrator always has access the WordPress admin area', 'userspace' ),
	],
] );

$options->box( 'primary' )->add_group( 'caching', [
	'title'  => __( 'Performance', 'userspace' ),
	'extend' => true,
] )->add_options( [
	[
		'type'      => 'switch',
		'slug'      => 'usp_use_cache',
		'title'     => __( 'Enable UserSpace caching', 'userspace' ),
		'help'      => __( 'Use the functionality of the caching UserSpace plugin. <a href="#" target="_blank">Read More</a>', 'userspace' ),
		'text'      => [
			'off' => __( 'No', 'userspace' ),
			'on'  => __( 'Yes', 'userspace' ),
		],
		'default'   => 0,
		'children' => [
			'cache_time',
			'cache_output',
		],
	],
	[
		'parent'  => [
			'id'    => 'use_cache',
			'value' => 1,
		],
		'type'    => 'number',
		'slug'    => 'usp_cache_time',
		'default' => 3600,
		'title'   => __( 'Time cache (seconds)', 'userspace' ),
		'notice'  => __( 'Default', 'userspace' ) . ': 3600',
	],
	[
		'parent'  => [
			'id'    => 'use_cache',
			'value' => 1,
		],
		'type'    => 'radio',
		'slug'    => 'usp_cache_output',
		'title'   => __( 'Cache output', 'userspace' ),
		'values'  => [
			__( 'All users', 'userspace' ),
			__( 'Only guests', 'userspace' ),
		],
		'default' => 0,
	],
	[
		'type'    => 'switch',
		'slug'    => 'usp_minify_css',
		'title'   => __( 'Combining & minimization css-files', 'userspace' ),
		'text'    => [
			'off' => __( 'No', 'userspace' ),
			'on'  => __( 'Yes', 'userspace' ),
		],
		'default' => 1,
		'help'    => __( 'Combining style files works if plugins support this feature', 'userspace' ),
	],
	[
		'type'    => 'switch',
		'slug'    => 'usp_minify_js',
		'title'   => __( 'Combining & minimization js-files', 'userspace' ),
		'text'    => [
			'off' => __( 'No', 'userspace' ),
			'on'  => __( 'Yes', 'userspace' ),
		],
		'default' => 0,
		'help'    => __( 'Combining js-files works if plugins support this feature', 'userspace' ),
	],
] );

$options->box( 'primary' )->add_group( 'system', [
	'title'  => __( 'System settings', 'userspace' ),
	'extend' => true,
] )->add_options( [
	[
		'type'    => 'switch',
		'slug'    => 'usp_logger',
		'title'   => __( 'Write background events and errors to the log-file', 'userspace' ),
		'text'    => [
			'off' => __( 'No', 'userspace' ),
			'on'  => __( 'Yes', 'userspace' ),
		],
		'default' => 0,
		'help'    => __( 'The log file is written to the directory: your-site/userspace/logs/', 'userspace' ),
	],
	[
		'title'    => __( 'The key of security for ajax-requests and other', 'userspace' ),
		'type'     => 'password',
		'required' => 1,
		'slug'     => 'usp_security_key',
	],
] );

$options->box( 'primary' )->add_group( 'emoji', [
	'title' => __( 'Twitter emoji', 'userspace' ),
] )->add_options( [
	[
		'type'    => 'switch',
		'slug'    => 'usp_emoji',
		'title'   => __( 'Use the Twitter Emoji on the website', 'userspace' ),
		'text'    => [
			'off' => __( 'No', 'userspace' ),
			'on'  => __( 'Yes', 'userspace' ),
		],
		'default' => 1,
		'help'    => __( 'A set of standard WordPress emoticons will be replaced with Twitter Emoji', 'userspace' ),
	],
] );

/**
 * The filter allows you to add new settings in the admin panel. On the plugin settings page.
 *
 * @param   $options    object  Settings object
 *
 * @see     OptionsManager
 *
 * @since   1.0.0
 */
$all_options = apply_filters( 'usp_options', $options );

$title = __( 'Configure UserSpace plugin and add-ons', 'userspace' );

$header = usp_get_admin_header( $title );

$content = usp_get_admin_content( $all_options->get_content() );

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo $header . $content;
