<?php

global $wpdb;

USP()->use_module( 'options-manager' );

usp_awesome_font_style();

wp_enqueue_script( 'jquery' );
wp_enqueue_script( 'jquery-ui-dialog' );
wp_enqueue_style( 'wp-jquery-ui-dialog' );

$usp_options = get_site_option( 'usp_global_options' );

$pages = usp_get_pages_ids();

$options = new USP_Options_Manager( [
    'option_name'  => 'usp_global_options',
    'page_options' => 'manage-userspace',
    'extends'      => true
    ] );

$options->add_box( 'primary', array(
    'title' => __( 'General settings', 'userspace' ),
    'icon'  => 'fa-cogs'
) )->add_group( 'office', array(
    'title'  => __( 'User profile page', 'userspace' ),
    'extend' => true
) )->add_options( array(
    $options->add_box( 'primary', array(
        'title' => __( 'General settings', 'userspace' ),
        'icon'  => 'fa-cogs'
    ) )->add_group( 'office', array(
        'title'  => __( 'User profile page', 'userspace' ),
        'extend' => true
    ) )->add_options( array(
        [
            'type'      => 'select',
            'slug'      => 'usp_profile_page_output',
            'title'     => __( 'User profile page output', 'userspace' ),
            'values'    => [
                'shortcode' => __( 'Using shortcode [userspace]', 'userspace' ),
                'authorphp' => __( 'On the author’s archive page', 'userspace' ),
            ],
            'help'      => __( 'Attention! Changing this parameter is not required. '
                . 'Detailed instructions on user profile output using author.php '
                . 'file can be received here <a href="#" target="_blank">here</a>', 'userspace' ),
            'notice'    => __( 'If author archive page is selected, the template author.php should contain the code <code>if(function_exists(\'userspace\')) userspace();</code>', 'userspace' ),
            'childrens' => array(
                'shortcode' => array(
                    [
                        'type'   => 'select',
                        'slug'   => 'usp_user_account_page',
                        'title'  => __( 'Page with shortcode for displaying user profile page', 'userspace' ),
                        'values' => $pages
                    ],
                    [
                        'type'  => 'text',
                        'slug'  => 'usp_user_account_slug',
                        'title' => __( 'Link format to user profile page', 'userspace' ),
                        'help'  => __( 'The link is formed according to principle "/slug_page/?get=ID". The parameter "get" can be set here. By default: "user"', 'userspace' )
                    ]
                )
            )
        ],
        [
            'type'      => 'runner',
            'slug'      => 'usp_user_timeout',
            'value_min' => 1,
            'value_max' => 20,
            'default'   => 10,
            'help'      => __( 'This value sets the maximum time a user is considered "online" in the absence of activity', 'userspace' ),
            'title'     => __( 'Current user inactivity timeout', 'userspace' ),
            'notice'    => __( 'Specify the time in minutes after which the user will be considered offline if you did not show activity on the website. The default is 10 minutes.', 'userspace' )
        ]
    ) )
) );

$options->box( 'primary' )->add_group( 'design', array(
    'title' => __( 'Design', 'userspace' ),
) )->add_options( array(
    [
        'slug'   => 'usp_current_office',
        'type'   => 'select',
        'title'  => __( 'Select a user profile page theme', 'userspace' ),
        'values' => USP()->themes()->get_themes()
    ],
    [
        'type'    => 'color',
        'slug'    => 'usp_primary_color',
        'title'   => __( 'Primary color', 'userspace' ),
        'default' => '#0369a1'
    ],
    [
        'type'       => 'uploader',
        'temp_media' => 1,
        'multiple'   => 0,
        'crop'       => 1,
        'filetitle'  => 'usp-default-avatar',
        'filename'   => 'usp-default-avatar',
        'slug'       => 'usp_default_avatar',
        'title'      => __( 'Default avatar', 'userspace' )
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
        '. ' . __( 'If 0 is specified, download is disallowed.', 'userspace' )
    ]
) );

$options->box( 'primary' )->add_group( 'usersign', array(
    'title' => __( 'Login and register', 'userspace' ),
) )->add_options( array(
    [
        'type'      => 'select',
        'slug'      => 'usp_login_form',
        'title'     => __( 'The order of output the form of login and registration', 'userspace' ),
        'values'    => [
            __( 'Floating form', 'userspace' ),
            __( 'On a separate page', 'userspace' ),
            __( 'Wordpress Forms', 'userspace' ),
            __( 'Widget form', 'userspace' )
        ],
        'notice'    => __( 'The form of login and registration of the plugin can be outputed with help of widget "Control panel" '
            . 'and a shortcode [usp-loginform], but you can use the standart login form of WordPress also', 'userspace' ),
        'childrens' => array(
            1 => array(
                [
                    'type'   => 'select',
                    'slug'   => 'usp_id_login_page',
                    'title'  => __( 'ID of the shortcode page [usp-loginform]', 'userspace' ),
                    'values' => $pages
                ]
            )
        )
    ],
    array(
        'type'    => 'switch',
        'slug'    => 'usp_confirm_register',
        'help'    => __( 'If this option is checked, newly registered users will receive a confirmation email to the email address specified during registration. This email contains a confirmation link that the user has to click, in order to activate the account.', 'userspace' ),
        'title'   => __( 'Registration requires email confirmation', 'userspace' ),
        'text'    => [
            'off' => __( 'No', 'userspace' ),
            'on'  => __( 'Yes', 'userspace' )
        ],
        'default' => 0,
    ),
    array(
        'type'      => 'radio',
        'slug'      => 'usp_authorize_page',
        'title'     => __( 'Redirect user after login', 'userspace' ),
        'values'    => [
            __( 'The user profile', 'userspace' ),
            __( 'Current page', 'userspace' ),
            __( 'Arbitrary URL', 'userspace' )
        ],
        'default'   => 0,
        'childrens' => array(
            2 => array(
                [
                    'type'   => 'text',
                    'slug'   => 'usp_custom_authorize_page',
                    'title'  => __( 'URL', 'userspace' ),
                    'notice' => __( 'Enter your URL below, if you select an arbitrary URL after login', 'userspace' )
                ]
            )
        )
    ),
    /* array(
      'type'	 => 'select',
      'slug'	 => 'repeat_pass',
      'title'	 => __( 'repeat password field', 'userspace' ),
      'values' => array( __( 'Disabled', 'userspace' ), __( 'Displaye', 'userspace' ) )
      ), */
//    array(
//        'type'   => 'select',
//        'slug'   => 'difficulty_parole',
//        'title'  => __( 'Password strength indicator', 'userspace' ),
//        'values' => array( __( 'Hide', 'userspace' ), __( 'Show', 'userspace' ) )
//    )
) );

$options->box( 'primary' )->add_group( 'usp_bar', array(
    'title' => __( 'UserSpace Bar', 'userspace' )
) )->add_options( array(
    [
        'type'      => 'switch',
        'slug'      => 'usp_bar_show',
        'title'     => __( 'Show UserSpace Bar when viewing site', 'userspace' ),
        'help'      => __( 'UserSpace Bar – is he top panel UserSpace plugin through which the plugin and its add-ons can output their data and the administrator can make his menu, forming it on <a href="/wp-admin/nav-menus.php" target="_blank">page management menu of the website</a>', 'userspace' ),
        'text'      => [
            'off' => __( 'No', 'userspace' ),
            'on'  => __( 'Yes', 'userspace' )
        ],
        'default'   => 0,
        'childrens' => array(
            1 => array(
                [
                    'type'    => 'radio',
                    'slug'    => 'usp_bar_color',
                    'title'   => __( 'Color', 'userspace' ),
                    'values'  => [
                        'dark'  => __( 'Dark', 'userspace' ),
                        'white' => __( 'White', 'userspace' ),
                        'color' => __( 'Primary colors of UserSpace', 'userspace' )
                    ],
                    'default' => 'dark',
                ],
                [
                    'type'   => 'number',
                    'slug'   => 'usp_bar_width',
                    'title'  => __( 'Width content area', 'userspace' ),
                    'help'   => __( 'Width in pixels. Default or 0: fullwidth. Example: 1280 (max width of your site)', 'userspace' ),
                    'notice' => __( 'Default: 0 (fullwidth)', 'userspace' ),
                ],
            )
        )
    ],
) );

$options->box( 'primary' )->add_group( 'access_console', array(
    'title' => __( 'Access to the console', 'userspace' ),
) )->add_options( array(
    [
        'type'   => 'checkbox',
        'slug'   => 'usp_consol_access',
        'title'  => __( 'Access to the console is allowed', 'userspace' ),
        'values' => usp_get_roles_ids( [ 'administrator', 'banned', 'need-confirm' ] ),
        'notice' => __( 'The administrator always has access the WordPress admin area', 'userspace' )
    ]
) );

$options->box( 'primary' )->add_group( 'caching', array(
    'title'  => __( 'Performance', 'userspace' ),
    'extend' => true
) )->add_options( array(
    [
        'type'      => 'switch',
        'slug'      => 'usp_use_cache',
        'title'     => __( 'Enable UserSpace caching', 'userspace' ),
        'help'      => __( 'Use the functionality of the caching UserSpace plugin. <a href="#" target="_blank">Read More</a>', 'userspace' ),
        'text'      => [
            'off' => __( 'No', 'userspace' ),
            'on'  => __( 'Yes', 'userspace' )
        ],
        'default'   => 0,
        'childrens' => [
            'cache_time', 'cache_output'
        ]
    ],
    [
        'parent'  => [
            'id'    => 'use_cache',
            'value' => 1
        ],
        'type'    => 'number',
        'slug'    => 'usp_cache_time',
        'default' => 3600,
        'title'   => __( 'Time cache (seconds)', 'userspace' ),
        'notice'  => __( 'Default', 'userspace' ) . ': 3600'
    ],
    [
        'parent'  => [
            'id'    => 'use_cache',
            'value' => 1
        ],
        'type'    => 'radio',
        'slug'    => 'usp_cache_output',
        'title'   => __( 'Cache output', 'userspace' ),
        'values'  => [
            __( 'All users', 'userspace' ),
            __( 'Only guests', 'userspace' )
        ],
        'default' => 0,
    ],
    [
        'type'    => 'switch',
        'slug'    => 'usp_minify_css',
        'title'   => __( 'Combining & minimization css-files', 'userspace' ),
        'text'    => [
            'off' => __( 'No', 'userspace' ),
            'on'  => __( 'Yes', 'userspace' )
        ],
        'default' => 1,
        'help'    => __( 'Combining style files works if plugins support this feature', 'userspace' )
    ],
    [
        'type'    => 'switch',
        'slug'    => 'usp_minify_js',
        'title'   => __( 'Combining & minimization js-files', 'userspace' ),
        'text'    => [
            'off' => __( 'No', 'userspace' ),
            'on'  => __( 'Yes', 'userspace' )
        ],
        'default' => 0,
        'help'    => __( 'Combining js-files works if plugins support this feature', 'userspace' )
    ]
) );

$options->box( 'primary' )->add_group( 'system', array(
    'title'  => __( 'System settings', 'userspace' ),
    'extend' => true
) )->add_options( array(
    [
        'type'    => 'switch',
        'slug'    => 'usp_logger',
        'title'   => __( 'Write background events and errors to the log-file', 'userspace' ),
        'text'    => [
            'off' => __( 'No', 'userspace' ),
            'on'  => __( 'Yes', 'userspace' )
        ],
        'default' => 0,
        'help'    => __( 'The log file is written to the address: your-site/userspace/logs/', 'userspace' )
    ],
    [
        'title'    => __( 'The key of security for ajax-requests and other', 'userspace' ),
        'type'     => 'password',
        'required' => 1,
        'slug'     => 'usp_security_key',
    ]
) );

$all_options = apply_filters( 'usp_options', $options );

$title = __( 'Configure UserSpace plugin and add-ons', 'userspace' );

$header = usp_get_admin_header( $title );

$content = usp_get_admin_content( $all_options->get_content() );

echo $header . $content;
