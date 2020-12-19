<?php

global $wpdb;

USP()->use_module( 'options-manager' );

//needed for the working of old cases
require_once USP_PATH . 'deprecated/class-usp-options.php';

usp_font_awesome_style();

wp_enqueue_script( 'jquery' );
wp_enqueue_script( 'jquery-ui-dialog' );
wp_enqueue_style( 'wp-jquery-ui-dialog' );

$usp_options = get_site_option( 'usp_global_options' );

$pages = usp_get_pages_ids();

$options = new USP_Options_Manager( array(
	'option_name'	 => 'usp_global_options',
	'page_options'	 => 'usp-options',
	'extends'		 => true
	) );

$options->add_box( 'primary', array(
	'title'	 => __( 'General settings', 'usp' ),
	'icon'	 => 'fa-cogs'
) )->add_group( 'office', array(
	'title'	 => __( 'Personal cabinet', 'usp' ),
	'extend' => true
) )->add_options( array(
	$options->add_box( 'primary', array(
		'title'	 => __( 'General settings', 'usp' ),
		'icon'	 => 'fa-cogs'
	) )->add_group( 'office', array(
		'title'	 => __( 'Personal cabinet', 'usp' ),
		'extend' => true
	) )->add_options( array(
		array(
			'type'		 => 'select',
			'slug'		 => 'view_user_lk_usp',
			'title'		 => __( 'Personal Cabinet output', 'usp' ),
			'values'	 => array(
				__( 'On the author’s archive page', 'usp' ),
				__( 'Using shortcode [wp-recall]', 'usp' ) ),
			'help'		 => __( 'Attention! Changing this parameter is not required. '
				. 'Detailed instructions on personal account output using author.php '
				. 'file can be received here <a href="https://codeseller.ru/post-group/ustanovka-plagina-wp-recall-na-sajt/ " target="_blank">here</a>', 'usp' ),
			'notice'	 => __( 'If author archive page is selected, the template author.php should contain the code if(function_exists(\'wp_recall\')) wp_recall();', 'usp' ),
			'childrens'	 => array(
				1 => array(
					array(
						'type'	 => 'select',
						'slug'	 => 'lk_page_usp',
						'title'	 => __( 'Shortcode host page', 'usp' ),
						'values' => $pages
					),
					array(
						'type'	 => 'text',
						'slug'	 => 'link_user_lk_usp',
						'title'	 => __( 'Link format to personal account', 'usp' ),
						'help'	 => __( 'The link is formed according to principle "/slug_page/?get=ID". The parameter "get" can be set here. By default user', 'usp' )
					)
				)
			)
		),
		array(
			'type'		 => 'runner',
			'slug'		 => 'timeout',
			'value_min'	 => 1,
			'value_max'	 => 20,
			'default'	 => 10,
			'help'		 => __( 'This value sets the maximum time a user is considered "online" in the absence of activity', 'usp' ),
			'title'		 => __( 'Inactivity timeout', 'usp' ),
			'notice'	 => __( 'Specify the time in minutes after which the user will be considered offline if you did not show activity on the website. The default is 10 minutes.', 'usp' )
		)
	) )
) );

$options->box( 'primary' )->add_group( 'security', array(
	'title'	 => __( 'Security', 'usp' ),
	'extend' => true
) )->add_options( array(
	array(
		'type'		 => 'password',
		'required'	 => 1,
		'slug'		 => 'security-key',
		'title'		 => __( 'The key of security for ajax-requests and other', 'usp' )
	)
) );

$options->box( 'primary' )->add_group( 'design', array(
	'title' => __( 'Design', 'usp' ),
) )->add_options( array(
	array(
		'slug'		 => 'current_theme',
		'type'	 => 'select',
		'title'	 => __( 'Текущая тема', 'usp' ),
		'values' => USP()->themes()->get_themes()
	),
	array(
		'type'		 => 'color',
		'slug'		 => 'primary-color',
		'title'		 => __( 'Primary color', 'usp' ),
		'default'	 => '#4C8CBD'
	),
	array(
		'type'	 => 'select',
		'slug'	 => 'buttons_place',
		'title'	 => __( 'The location of the section buttons', 'usp' ),
		'values' => array(
			__( 'Top', 'usp' ),
			__( 'Left', 'usp' ) )
	),
	array(
		'type'		 => 'uploader',
		'temp_media' => 1,
		'multiple'	 => 0,
		'crop'		 => 1,
		'filetitle'	 => 'usp-default-avatar',
		'filename'	 => 'usp-default-avatar',
		'slug'		 => 'default_avatar',
		'title'		 => __( 'Default avatar', 'usp' )
	),
	array(
		'type'		 => 'runner',
		'value_min'	 => 0,
		'value_max'	 => 5120,
		'value_step' => 256,
		'default'	 => 1024,
		'slug'		 => 'avatar_weight',
		'title'		 => __( 'Max weight of avatars', 'usp' ) . ', Kb',
		'notice'	 => __( 'Set the image upload limit in kb, by default', 'usp' ) . ' 1024Kb' .
		'. ' . __( 'If 0 is specified, download is disallowed.', 'usp' )
	)
) );

$options->box( 'primary' )->add_group( 'usersign', array(
	'title' => __( 'Login and register', 'usp' ),
) )->add_options( array(
	array(
		'type'		 => 'select',
		'slug'		 => 'login_form_recall',
		'title'		 => __( 'The order of output the form of login and registration', 'usp' ),
		'values'	 => array(
			__( 'Floating form', 'usp' ),
			__( 'On a separate page', 'usp' ),
			__( 'Wordpress Forms', 'usp' ),
			__( 'Widget form', 'usp' ) ),
		'notice'	 => __( 'The form of login and registration of the plugin can be outputed with help of widget "Control panel" '
			. 'and a shortcode [loginform], but you can use the standart login form of WordPress also', 'usp' ),
		'childrens'	 => array(
			1 => array(
				array(
					'type'	 => 'select',
					'slug'	 => 'page_login_form_recall',
					'title'	 => __( 'ID of the shortcode page [loginform]', 'usp' ),
					'values' => $pages
				)
			)
		)
	),
	array(
		'type'	 => 'select',
		'slug'	 => 'confirm_register_recall',
		'help'	 => __( 'If you are using the registration confirmation, after registration, the user will need to confirm your email by clicking on the link in the sent email', 'usp' ),
		'title'	 => __( 'Registration confirmation by the user', 'usp' ),
		'values' => array(
			__( 'Not used', 'usp' ),
			__( 'Used', 'usp' ) )
	),
	array(
		'type'		 => 'select',
		'slug'		 => 'authorize_page',
		'title'		 => __( 'Redirect user after login', 'usp' ),
		'values'	 => array(
			__( 'The user profile', 'usp' ),
			__( 'Current page', 'usp' ),
			__( 'Arbitrary URL', 'usp' ) ),
		'childrens'	 => array(
			2 => array(
				array(
					'type'	 => 'text',
					'slug'	 => 'custom_authorize_page',
					'title'	 => __( 'URL', 'usp' ),
					'notice' => __( 'Enter your URL below, if you select an arbitrary URL after login', 'usp' )
				)
			)
		)
	),
	/* array(
	  'type'	 => 'select',
	  'slug'	 => 'repeat_pass',
	  'title'	 => __( 'repeat password field', 'usp' ),
	  'values' => array( __( 'Disabled', 'usp' ), __( 'Displaye', 'usp' ) )
	  ),
	  array(
	  'type'	 => 'select',
	  'slug'	 => 'difficulty_parole',
	  'title'	 => __( 'Indicator of password complexity', 'usp' ),
	  'values' => array( __( 'Disabled', 'usp' ), __( 'Displaye', 'usp' ) )
	  ) */
) );

$options->box( 'primary' )->add_group( 'recallbar', array(
	'title' => __( 'Recallbar', 'usp' )
) )->add_options( array(
	array(
		'type'		 => 'select',
		'slug'		 => 'view_recallbar',
		'title'		 => __( 'Output of recallbar panel', 'usp' ),
		'help'		 => __( 'Recallbar – is he top panel WP-Recall plugin through which the plugin and its add-ons can output their data and the administrator can make his menu, forming it on <a href="/wp-admin/nav-menus.php" target="_blank">page management menu of the website</a>', 'usp' ),
		'values'	 => array( __( 'Disabled', 'usp' ), __( 'Enabled', 'usp' ) ),
		'childrens'	 => array(
			'rcb_color'
		)
	),
	array(
		'parent' => array(
			'id'	 => 'view_recallbar',
			'value'	 => 1
		),
		'type'	 => 'select',
		'slug'	 => 'rcb_color',
		'title'	 => __( 'Color', 'usp' ),
		'values' => array( __( 'Default', 'usp' ), __( 'Primary colors of WP-Recall', 'usp' ) )
	)
) );

$options->box( 'primary' )->add_group( 'caching', array(
	'title'	 => __( 'Caching', 'usp' ),
	'extend' => true
) )->add_options( array(
	array(
		'type'		 => 'select',
		'slug'		 => 'use_cache',
		'title'		 => __( 'Cache', 'usp' ),
		'help'		 => __( 'Use the functionality of the caching WP-Recall plugin. <a href="https://codeseller.ru/post-group/funkcional-keshirovaniya-plagina-wp-recall/" target="_blank">read More</a>', 'usp' ),
		'values'	 => array(
			__( 'Disabled', 'usp' ),
			__( 'Enabled', 'usp' ) ),
		'childrens'	 => array(
			'cache_time', 'cache_output'
		)
	),
	array(
		'parent'	 => array(
			'id'	 => 'use_cache',
			'value'	 => 1
		),
		'type'		 => 'number',
		'slug'		 => 'cache_time',
		'default'	 => 3600,
		'latitlebel' => __( 'Time cache (seconds)', 'usp' ),
		'notice'	 => __( 'Default', 'usp' ) . ': 3600'
	),
	array(
		'parent' => array(
			'id'	 => 'use_cache',
			'value'	 => 1
		),
		'type'	 => 'select',
		'slug'	 => 'cache_output',
		'title'	 => __( 'Cache output', 'usp' ),
		'values' => array(
			__( 'All users', 'usp' ),
			__( 'Only guests', 'usp' ) )
	),
	array(
		'type'	 => 'select',
		'slug'	 => 'minify_css',
		'title'	 => __( 'Minimization of file styles', 'usp' ),
		'values' => array(
			__( 'Disabled', 'usp' ),
			__( 'Enabled', 'usp' ) ),
		'notice' => __( 'Minimization of file styles only works in correlation with WP-Recall style files and add-ons that support this feature', 'usp' )
	),
	array(
		'type'	 => 'select',
		'slug'	 => 'minify_js',
		'title'	 => __( 'Minimization of scripts', 'usp' ),
		'values' => array(
			__( 'Disabled', 'usp' ),
			__( 'Enabled', 'usp' ) )
	)
) );

$options->box( 'primary' )->add_group( 'access_console', array(
	'title' => __( 'Access to the console', 'usp' ),
) )->add_options( array(
	array(
		'type'	 => 'checkbox',
		'slug'	 => 'consol_access_usp',
		'title'	 => __( 'Access to the console is allowed', 'usp' ),
		'values' => usp_get_roles_ids(),
		'notice' => __( 'Администратор всегда имеет доступ в административную часть', 'usp' )
	)
) );

$options->box( 'primary' )->add_group( 'logging', array(
	'title'	 => __( 'Logging mode', 'usp' ),
	'extend' => true
) )->add_options( array(
	array(
		'type'	 => 'select',
		'slug'	 => 'usp-log',
		'title'	 => __( 'Write background events and errors to the log-file', 'usp' ),
		'values' => array(
			__( 'Disabled', 'usp' ),
			__( 'Enabled', 'usp' )
		)
	)
) );

/* support old options */
global $uspOldOptionData;

apply_filters( 'admin_options_wprecall', '' );

if ( $uspOldOptionData ) {

	foreach ( $uspOldOptionData as $box_id => $box ) {

		if ( ! $box['groups'] )
			continue;

		$options->add_box( $box_id, array(
			'title' => $box['title']
		) );

		foreach ( $box['groups'] as $k => $group ) {

			$options->box( $box_id )->add_group( $k, array(
				'title' => $group['title']
			) )->add_options( $group['options'] );
		}
	}
}

unset( $uspOldOptionData );
/* * * */

$options = apply_filters( 'usp_options', $options );

$content = '<h2>' . __( 'Configure WP-Recall plugin and add-ons', 'usp' ) . '</h2>';

$content .= $options->get_content();

echo $content;
