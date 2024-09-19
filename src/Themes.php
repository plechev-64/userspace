<?php

class Themes {
	public function get_themes(): array {

		require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

		$plugins = get_plugins();

		$themes = array( 'userspace/themes/default/index.php' => __( 'Default Theme', 'userspace' ) );

		foreach ( $plugins as $key => $plugin ) {
			if ( ! $plugin['UserSpaceTheme'] || ! is_plugin_active( $key ) ) {
				continue;
			}
			$themes[ $key ] = $plugin['Name'];
		}

		return $themes;
	}

	public function get_current(): Theme {

		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		$current_id = usp_get_option( 'usp_current_office' );

		if ( ! is_plugin_active( $current_id ) ) {
			$current_id = 'userspace/themes/default/index.php';
			require_once USP_PATH . 'themes/default/index.php';
		}

		$current_theme = apply_filters( 'usp_current_office', $current_id );

		return new Theme( array(
			'id'   => $current_theme,
			'path' => wp_normalize_path( dirname( plugin_dir_path( __FILE__ ), 2 ) . '/' . $current_theme )
		) );
	}

}
