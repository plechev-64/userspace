<?php

class USP_Install {
	public static function init() {
		add_action( 'init', array( __CLASS__, 'init_global' ) );
		add_filter( 'wpmu_drop_tables', array( __CLASS__, 'wpmu_drop_tables' ) );
	}

	public static function install() {

		if ( ! defined( 'USP_INSTALLING' ) ) {
			define( 'USP_INSTALLING', true );
		}

		USP()->init();

		//FIXME: Deal with these global ones. Whether they are needed here is still unclear.
		self::init_global();

		self::create_tables();
		self::create_roles();

		if ( usp_get_option( 'usp_profile_page_output', 'shortcode' ) == 'shortcode' ) {
			self::create_pages();
		}

		self::any_functions();

		self::create_files();
	}

	public static function init_global() {
		$upload_dir = usp_get_wp_upload_dir();
		wp_mkdir_p( ( $upload_dir['basedir'] ) );
	}

	public static function create_tables() {
		global $wpdb;

		$wpdb->hide_errors();

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		foreach ( self::get_schema() as $shema ) {
			dbDelta( $shema );
		}
	}

	private static function get_schema() {
		global $wpdb;

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			if ( ! empty( $wpdb->charset ) ) {
				$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if ( ! empty( $wpdb->collate ) ) {
				$collate .= " COLLATE $wpdb->collate";
			}
		}

		return array(
			"
			CREATE TABLE IF NOT EXISTS `" . USP_PREF . "users_actions` (
				actid BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				user_id BIGINT(20) UNSIGNED NOT NULL,
				date_action DATETIME NOT NULL,
				PRIMARY KEY  actid (actid),
				UNIQUE KEY user_id (user_id),
				KEY date_action (date_action)
			) $collate",
			"CREATE TABLE IF NOT EXISTS `" . USP_PREF . "temp_media` (
				media_id BIGINT(20) UNSIGNED NOT NULL,
				user_id BIGINT(20) UNSIGNED NOT NULL,
				session_id VARCHAR(200) NOT NULL,
				uploader_id VARCHAR(200) NOT NULL,
				upload_date DATETIME NOT NULL,
				UNIQUE KEY  media_id (media_id),
				KEY upload_date (upload_date)
			) $collate"
		);
	}

	private static function create_pages() {

		$pages = apply_filters( 'usp_pages', array(
			'account_page' => array(
				'name'    => 'account',
				'title'   => __( 'User profile page', 'userspace' ),
				'content' => '<!-- wp:shortcode -->[userspace]<!-- /wp:shortcode -->'
			)
		) );

		foreach ( $pages as $key => $page ) {

			if ( is_array( $page ) ) {

				if ( ! usp_isset_plugin_page( $key ) ) {

					$page_id = usp_create_plugin_page_if_need( $key, [
						'post_title'   => $page['title'],
						'post_content' => $page['content'],
						'post_name'    => $page['name'],
					] );

					usp_update_option( $key, $page_id );
				}
			}
		}
	}

	private static function create_files() {
		$upload_dir = USP()->upload_dir();

		$files = array(
			array(
				'base'    => $upload_dir['basedir'],
				'file'    => 'index.html',
				'content' => ''
			),
			array(
				'base'    => USP_TAKEPATH,
				'file'    => '.htaccess',
				'content' => 'Options -indexes'
			),
			array(
				'base'    => USP_TAKEPATH,
				'file'    => 'index.html',
				'content' => ''
			),
			array(
				'base'    => USP_TAKEPATH . 'templates',
				'file'    => 'index.html',
				'content' => ''
			),
			array(
				'base'    => USP_UPLOAD_PATH,
				'file'    => 'index.html',
				'content' => ''
			)
		);

		foreach ( $files as $file ) {
			if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
				if ( $file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' ) ) {
					fwrite( $file_handle, $file['content'] );
					fclose( $file_handle );
				}
			}
		}
	}

	public static function create_roles() {

		if ( ! class_exists( 'WP_Roles' ) ) {
			return;
		}

		add_role( 'need-confirm', __( 'Unconfirmed', 'userspace' ), array(
				'read'         => false,
				'edit_posts'   => false,
				'delete_posts' => false,
				'upload_files' => false
			)
		);

		add_role( 'banned', __( 'Ban', 'userspace' ), array(
				'read'         => false,
				'edit_posts'   => false,
				'delete_posts' => false,
				'upload_files' => false
			)
		);
	}

	/**
	 * Deleting tables if the blog is deleted (for multi-sites)
	 *
	 * @param   array  $tables
	 *
	 * @return array
	 */
	public static function wpmu_drop_tables( $tables ) {
		$tables[] = USP_PREF . 'users_actions';

		return $tables;
	}

	/*
	  Here I decided to add functions that are incomprehensible to me when installing the plugin
	  In the future, you need to redefine the dependencies and rewrite everything here
	 */
	private static function any_functions() {
		global $wpdb;

		if ( ! usp_get_option( 'usp_security_key' ) ) {
			usp_update_option( 'usp_security_key', wp_generate_password( 20, false ) );
		}

		// create autoload global options
		if ( ! is_multisite() ) {
			$wpdb->update(
				$wpdb->options,
				[ 'autoload' => 'yes' ],
				[ 'option_name' => 'usp_global_options' ]
			);
		}

		if ( usp_get_option( 'usp_profile_page_output', 'shortcode' ) == 'shortcode' ) {
			// disable the display of the admin panel for all users of the site, if enabled
			$wpdb->update(
				$wpdb->usermeta,
				[ 'meta_value' => 'false' ],
				[ 'meta_key' => 'show_admin_bar_front' ]
			);

			update_site_option( 'default_role', 'author' );
			update_site_option( 'users_can_register', 1 );
		} else {

			// setting up the display of avatars on the site
			update_site_option( 'show_avatars', 1 );
		}

		update_site_option( 'usp_version', USP_VERSION );

		usp_remove_dir( USP_UPLOAD_PATH . 'js' );
		usp_remove_dir( USP_UPLOAD_PATH . 'css' );
	}

}

USP_Install::init();
