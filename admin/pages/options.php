<?php

global $wpdb;

USP()->use_module( 'options-manager' );

usp_font_awesome_style();

wp_enqueue_script( 'jquery' );
wp_enqueue_script( 'jquery-ui-dialog' );
wp_enqueue_style( 'wp-jquery-ui-dialog' );

$usp_options = get_site_option( 'usp_global_options' );

$pages = usp_get_pages_ids();

$options = new USP_Options_Manager( [
    'option_name'  => 'usp_global_options',
    'page_options' => 'usp-options',
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
            'slug'      => 'usp_type_output_user_account',
            'title'     => __( 'User profile page output', 'userspace' ),
            'values'    => [
                'shortcode' => __( 'Using shortcode [userspace]', 'userspace' ),
                'authorphp' => __( 'On the author’s archive page', 'userspace' ),
            ],
            'help'      => __( 'Attention! Changing this parameter is not required. '
                . 'Detailed instructions on user profile output using author.php '
                . 'file can be received here <a href="#" target="_blank">here</a>', 'userspace' ),
            'notice'    => __( 'If author archive page is selected, the template author.php should contain the code if(function_exists(\'userspace\')) userspace();', 'userspace' ),
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
                        'help'  => __( 'The link is formed according to principle "/slug_page/?get=ID". The parameter "get" can be set here. By default user', 'userspace' )
                    ]
                )
            )
        ],
        [
            'type'      => 'runner',
            'slug'      => 'timeout',
            'value_min' => 1,
            'value_max' => 20,
            'default'   => 10,
            'help'      => __( 'This value sets the maximum time a user is considered "online" in the absence of activity', 'userspace' ),
            'title'     => __( 'Current user inactivity timeout', 'userspace' ),
            'notice'    => __( 'Specify the time in minutes after which the user will be considered offline if you did not show activity on the website. The default is 10 minutes.', 'userspace' )
        ]
    ) )
) );

$options->box( 'primary' )->add_group( 'security', array(
    'title'  => __( 'Security', 'userspace' ),
    'extend' => true
) )->add_options( array(
    [
        'type'     => 'password',
        'required' => 1,
        'slug'     => 'security-key',
        'title'    => __( 'The key of security for ajax-requests and other', 'userspace' )
    ]
) );

$options->box( 'primary' )->add_group( 'design', array(
    'title' => __( 'Design', 'userspace' ),
) )->add_options( array(
    [
        'slug'   => 'usp-current-office',
        'type'   => 'select',
        'title'  => __( 'Select a user profile page theme', 'userspace' ),
        'values' => USP()->themes()->get_themes()
    ],
    [
        'type'    => 'color',
        'slug'    => 'usp-primary-color',
        'title'   => __( 'Primary color', 'userspace' ),
        'default' => '#0369a1'
    ],
    [
        'type'    => 'radio',
        'slug'    => 'usp_office_tab_type',
        'title'   => __( 'The location of the section buttons', 'userspace' ),
        'values'  => [ __( 'Top', 'userspace' ), __( 'Left', 'userspace' ) ],
        'default' => 0,
    ],
    [
        'type'       => 'uploader',
        'temp_media' => 1,
        'multiple'   => 0,
        'crop'       => 1,
        'filetitle'  => 'usp-default-avatar',
        'filename'   => 'usp-default-avatar',
        'slug'       => 'default_avatar',
        'title'      => __( 'Default avatar', 'userspace' )
    ],
    [
        'type'       => 'runner',
        'value_min'  => 0,
        'value_max'  => 5120,
        'value_step' => 256,
        'default'    => 1024,
        'slug'       => 'avatar_weight',
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
            . 'and a shortcode [loginform], but you can use the standart login form of WordPress also', 'userspace' ),
        'childrens' => array(
            1 => array(
                [
                    'type'   => 'select',
                    'slug'   => 'usp_id_login_page',
                    'title'  => __( 'ID of the shortcode page [loginform]', 'userspace' ),
                    'values' => $pages
                ]
            )
        )
    ],
    array(
        'type'   => 'select',
        'slug'   => 'usp_confirm_register',
        'help'   => __( 'If you are using the registration confirmation, after registration, the user will need to confirm your email by clicking on the link in the sent email', 'userspace' ),
        'title'  => __( 'Registration confirmation by the user', 'userspace' ),
        'values' => [
            __( 'Not used', 'userspace' ),
            __( 'Used', 'userspace' )
        ]
    ),
    array(
        'type'      => 'select',
        'slug'      => 'authorize_page',
        'title'     => __( 'Redirect user after login', 'userspace' ),
        'values'    => [
            __( 'The user profile', 'userspace' ),
            __( 'Current page', 'userspace' ),
            __( 'Arbitrary URL', 'userspace' )
        ],
        'childrens' => array(
            2 => array(
                [
                    'type'   => 'text',
                    'slug'   => 'custom_authorize_page',
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
        'type'      => 'radio',
        'slug'      => 'view_usp_bar',
        'title'     => __( 'Show UserSpace Bar when viewing site', 'userspace' ),
        'help'      => __( 'UserSpace Bar – is he top panel UserSpace plugin through which the plugin and its add-ons can output their data and the administrator can make his menu, forming it on <a href="/wp-admin/nav-menus.php" target="_blank">page management menu of the website</a>', 'userspace' ),
        'values'    => [
            __( 'Hide', 'userspace' ),
            __( 'Show', 'userspace' )
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

$options->box( 'primary' )->add_group( 'caching', array(
    'title'  => __( 'Caching', 'userspace' ),
    'extend' => true
) )->add_options( array(
    [
        'type'      => 'select',
        'slug'      => 'use_cache',
        'title'     => __( 'Cache', 'userspace' ),
        'help'      => __( 'Use the functionality of the caching UserSpace plugin. <a href="#" target="_blank">read More</a>', 'userspace' ),
        'values'    => [
            __( 'Disabled', 'userspace' ),
            __( 'Enabled', 'userspace' )
        ],
        'childrens' => [
            'cache_time', 'cache_output'
        ]
    ],
    [
        'parent'     => [
            'id'    => 'use_cache',
            'value' => 1
        ],
        'type'       => 'number',
        'slug'       => 'cache_time',
        'default'    => 3600,
        'latitlebel' => __( 'Time cache (seconds)', 'userspace' ),
        'notice'     => __( 'Default', 'userspace' ) . ': 3600'
    ],
    [
        'parent' => [
            'id'    => 'use_cache',
            'value' => 1
        ],
        'type'   => 'select',
        'slug'   => 'cache_output',
        'title'  => __( 'Cache output', 'userspace' ),
        'values' => [
            __( 'All users', 'userspace' ),
            __( 'Only guests', 'userspace' )
        ]
    ],
    [
        'type'   => 'select',
        'slug'   => 'minify_css',
        'title'  => __( 'Minimization of file styles', 'userspace' ),
        'values' => [
            __( 'Disabled', 'userspace' ),
            __( 'Enabled', 'userspace' )
        ],
        'notice' => __( 'Minimization of file styles only works in correlation with UserSpace style files and add-ons that support this feature', 'userspace' )
    ],
    [
        'type'   => 'select',
        'slug'   => 'minify_js',
        'title'  => __( 'Minimization of scripts', 'userspace' ),
        'values' => [
            __( 'Disabled', 'userspace' ),
            __( 'Enabled', 'userspace' )
        ]
    ]
) );

$options->box( 'primary' )->add_group( 'access_console', array(
    'title' => __( 'Access to the console', 'userspace' ),
) )->add_options( array(
    [
        'type'   => 'checkbox',
        'slug'   => 'consol_access_usp',
        'title'  => __( 'Access to the console is allowed', 'userspace' ),
        'values' => usp_get_roles_ids(),
        'notice' => __( 'The administrator always has access the WordPress admin area', 'userspace' )
    ]
) );

$options->box( 'primary' )->add_group( 'logging', array(
    'title'  => __( 'Logging mode', 'userspace' ),
    'extend' => true
) )->add_options( array(
    [
        'type'   => 'select',
        'slug'   => 'usp-log',
        'title'  => __( 'Write background events and errors to the log-file', 'userspace' ),
        'values' => [
            __( 'Disabled', 'userspace' ),
            __( 'Enabled', 'userspace' )
        ]
    ]
) );

$all_options = apply_filters( 'usp_options', $options );

$content = $all_options->get_content();

$title = '<h2>' . __( 'Configure UserSpace plugin and add-ons', 'userspace' ) . '</h2>';

echo $title . $content;
