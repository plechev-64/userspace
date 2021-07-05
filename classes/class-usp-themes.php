<?php

class USP_Themes {
    function get_themes() {

        require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

        $plugins = get_plugins();

        $themes = array( 'userspace/themes/default/index.php' => __( 'Default Theme', 'userspace' ) );

        foreach ( $plugins as $key => $plugin ) {
            if ( ! $plugin['UserSpaceTheme'] || ! is_plugin_active( $key ) )
                continue;
            $themes[$key] = $plugin['Name'];
        }

        return $themes;
    }

    function get_current() {

        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        $current_id = usp_get_option( 'usp_current_office' );

        if ( ! is_plugin_active( $current_id ) ) {
            $current_id = 'userspace/themes/default/index.php';
            require_once USP_PATH . 'themes/default/index.php';
        }

        $current_theme = apply_filters( 'usp_current_office', $current_id );

        return new USP_Theme( array(
            'id'   => $current_theme,
            'path' => wp_normalize_path( dirname( dirname( plugin_dir_path( __FILE__ ) ) ) . '/' . $current_theme )
            ) );
    }

}
