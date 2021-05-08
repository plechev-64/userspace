<?php

add_action( 'usp_enqueue_scripts', 'usp_userspace_bar_scripts', 10 );
function usp_userspace_bar_scripts() {
    usp_enqueue_style( 'usp-bar', USP_URL . 'modules/usp-bar/style.css', false, false, true );
}

add_action( 'wp_footer', 'usp_userspace_bar_menu', 3 );
function usp_userspace_bar_menu() {
    usp_include_template( 'usp-bar.php' );
}

add_action( 'wp', 'usp_bar_setup', 10 );
function usp_bar_setup() {
    do_action( 'usp_bar_setup' );
}

add_action( 'usp_bar_setup', 'usp_setup_bar_default_data', 10 );
function usp_setup_bar_default_data() {
    if ( ! is_user_logged_in() )
        return;

    global $usp_user_URL;

    usp_bar_add_menu_item( 'account-link', [
        'url'   => $usp_user_URL,
        'icon'  => 'fa-user',
        'label' => __( 'Go to personal account', 'userspace' )
        ]
    );
}

add_action( 'usp_bar_setup', 'usp_setup_bar_add_admin_link', 50 );
function usp_setup_bar_add_admin_link() {
    if ( ! is_user_logged_in() )
        return;

    if ( ! current_user_can( 'activate_plugins' ) )
        return;

    usp_bar_add_menu_item( 'admin-link', [
        'url'   => admin_url(),
        'icon'  => 'fa-external-link-square',
        'label' => __( 'To admin area', 'userspace' )
        ]
    );
}

add_action( 'usp_bar_print_icons', 'usp_print_bar_icons', 10 );
function usp_print_bar_icons() {
    global $usp_bar;
    if ( ! isset( $usp_bar['icons'] ) || ! $usp_bar['icons'] )
        return false;

    if ( is_array( $usp_bar['icons'] ) ) {

        $usp_bar_icons = apply_filters( 'usp_bar_icons', $usp_bar['icons'] );

        foreach ( $usp_bar_icons as $id_icon => $icon ) {
            if ( ! isset( $icon['icon'] ) )
                continue;

            $class = (isset( $icon['class'] )) ? $icon['class'] : '';

            echo '<div id="' . $id_icon . '" class="rcb_icon ' . $class . '">';

            if ( isset( $icon['url'] ) || isset( $icon['onclick'] ) ):

                $url     = isset( $icon['url'] ) ? $icon['url'] : '#';
                $onclick = isset( $icon['onclick'] ) ? 'onclick="' . $icon['onclick'] . ';return false;"' : '';

                echo '<a href="' . $url . '" ' . $onclick . '>';

            endif;

            echo '<i class="uspi ' . $icon['icon'] . '" aria-hidden="true"></i>';
            echo '<span>';

            if ( isset( $icon['label'] ) ):
                echo $icon['label'];
            endif;

            echo '</span>';

            if ( isset( $icon['url'] ) || isset( $icon['onclick'] ) ):
                echo '</a>';
            endif;

            if ( isset( $icon['counter'] ) ):
                echo '<div class="rcb_nmbr ' . ($icon['counter'] > 0 ? 'counter_not_null' : '') . '">' . $icon['counter'] . '</div>';
            endif;

            echo '</div>';
        }
    }
}

add_action( 'usp_bar_print_menu', 'usp_print_bar_right_menu', 10 );
function usp_print_bar_right_menu() {
    global $usp_bar;
    if ( ! isset( $usp_bar['menu'] ) || ! $usp_bar['menu'] )
        return;

    if ( ! is_array( $usp_bar['menu'] ) )
        return;

    $usp_bar_menu = apply_filters( 'usp_bar_menu', $usp_bar['menu'] );

    foreach ( $usp_bar_menu as $icon ) {
        if ( ! isset( $icon['url'] ) )
            continue;

        $args = [
            'type'  => 'clear',
            'label' => $icon['label'],
            'size'  => 'medium',
            'href'  => $icon['url'],
            'class' => 'rcb_line'
        ];

        if ( isset( $icon['icon'] ) ) {
            $args['icon'] = $icon['icon'];
        }

        echo usp_get_button( $args );
    }
}

// remove offset in wordpress toolbar
add_action( 'get_header', 'usp_bar_remove_admin_bar' );
function usp_bar_remove_admin_bar() {
    if ( ! is_admin_bar_showing() )
        return;

    remove_action( 'wp_head', '_admin_bar_bump_cb' );
}

// registering our offsets
add_filter( 'usp_inline_styles', 'usp_bar_offset_inline_styles', 10 );
function usp_bar_offset_inline_styles( $styles ) {
    if ( is_admin_bar_showing() ) {
        // 32px wordpress toolbar + 40px (min) UserSpace bar = 72px
        // on 782px: 46 + 40 = 86
        $styles .= 'html {margin-top:72px !important;}
        * html body {margin-top:72px !important;}
        #usp-bar{margin-top:32px;}
        @media screen and (max-width:782px) {
        html {margin-top: 86px !important;}
        * html body {margin-top: 86px !important;}
        #usp-bar{margin-top:46px;}
        }';
    } else {
        $styles .= 'html {margin-top:40px !important;}
        * html body {margin-top:40px !important;}';
    }

    return $styles;
}

// add a submenu class (as in the standard userspace menu)
add_filter( 'nav_menu_submenu_css_class', 'usp_bar_rename_submenu_class', 10, 2 );
function usp_bar_rename_submenu_class( $classes, $args ) {
    if ( $args->theme_location !== 'usp-bar' )
        return $classes;

    foreach ( $classes as $key => $class ) {
        if ( $class == 'sub-menu' ) {
            $classes[$key] = 'usp-sub-menu';
        }
    }

    return $classes;
}

// add menu has children class
add_filter( 'wp_nav_menu_objects', 'usp_bar_add_class_in_parent_item', 10, 2 );
function usp_bar_add_class_in_parent_item( $sorted_menu_items, $args ) {
    if ( $args->theme_location !== 'usp-bar' )
        return $sorted_menu_items;

    foreach ( $sorted_menu_items as $item ) {
        if ( __find_is_has_child( $item->ID, $sorted_menu_items ) )
            $item->classes[] = 'usp-menu-has-child';
    }

    return $sorted_menu_items;
}

// helper on usp_bar_add_class_in_parent_item functions
function __find_is_has_child( $item_id, $sorted_menu_items ) {
    foreach ( $sorted_menu_items as $item ) {
        if ( $item->menu_item_parent && $item->menu_item_parent == $item_id )
            return true;
    }

    return false;
}

// added class in body tag
add_filter( 'body_class', 'usp_add_userbar_class_body' );
function usp_add_userbar_class_body( $classes ) {
    $classes[] = 'usp-userbar';
    $classes[] = 'usp-userbar-' . usp_get_option( 'usp_bar_color', 'dark' );

    return $classes;
}
