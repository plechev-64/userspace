<?php
/*
  Plugin Name: UserSpace
  Plugin URI: http://user-space.com/
  Description: Login & registration form, profile fields, front-end profile, user account and core for WordPress membership.
  Version: 0.1
  Author: Plechev Andrey
  Author URI: http://user-space.com/
  Text Domain: userspace
  License: GPLv2 or later (license.txt)
 */

/*  Copyright 2012  Plechev Andrey  (email : support {at} codeseller.ru)  */

final class UserSpace {

	private $version = '1.0.0';
	private $theme = null;
	private $fields = [];
	private $modules = [];
	private $used_modules = [];
	private static $instance = null;

	public static function getInstance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		if ( self::$instance ) {
			return;
		}

		$this->define_constants(); // Defining constants.
		$this->includes(); // Connecting all the necessary files with functions and classes
		$this->load_options(); // Load options.
		$this->init_modules(); // Defining modules.
		$this->init_hooks(); // Defining hooks

	}

	public function __clone() {
		return;
	}

	public function __wakeup() {
		return;
	}

	private function define_constants() {
		global $wpdb;

		$upload_dir = $this->upload_dir();

		$this->define( 'USP_VERSION', $this->version );

		$this->define( 'USP_URL', trailingslashit( plugins_url( '/', __FILE__ ) ) );
		$this->define( 'USP_PREF', $wpdb->base_prefix . 'usp_' );

		$this->define( 'USP_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
		$this->define( 'USP_UPLOAD_PATH', $upload_dir['basedir'] . '/usp-uploads/' );
		$this->define( 'USP_UPLOAD_URL', $upload_dir['baseurl'] . '/usp-uploads/' );

		$this->define( 'USP_TAKEPATH', WP_CONTENT_DIR . '/userspace/' );
	}

	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	private function load_options() {
		Options::getInstance();
	}

	private function init_hooks() {
		register_activation_hook( __FILE__, [ 'Install', 'install' ] );

		add_action( 'init', [ $this, 'init' ], 0 );

		add_action( 'usp_area_before', [ $this, 'userspace_office_load' ] );

		/**
		 * Register our extra header for themes
		 *
		 * @since 1.0
		 */
		add_filter( 'extra_plugin_headers', [ $this, 'register_theme_header' ] );

		if ( ! is_admin() ) {
			add_action( 'usp_enqueue_scripts', 'usp_core_resources', 1 );
			add_action( 'usp_enqueue_scripts', 'usp_frontend_scripts', 1 );
			add_action( 'wp_head', [ $this, 'update_user_activity' ], 10 );
		}
	}

	function update_user_activity() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		usp_user_update_activity();
	}

	function register_theme_header( $extra_context_headers ) {
		$extra_context_headers['UserSpaceTheme'] = 'UserSpaceTheme';

		return $extra_context_headers;
	}

	/*
	 * Find out the type of request
	 */
	private function is_request( $type ) {
		switch ( $type ) {
			case 'admin' :
				return is_admin();
			case 'ajax' :
				return defined( 'DOING_AJAX' );
			case 'cron' :
				return defined( 'DOING_CRON' );
			case 'frontend' :
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
	}

	//all files for the admin panel
	public function admin_includes() {
		require_once 'admin/index.php';
	}

	public function userspace_office_load() {
		if ( $this->office()->is_owner( get_current_user_id() ) ) {
			$this->use_module( 'forms' );
		}
	}

	public function init() {
		do_action( 'usp_before_init' );

		$this->fields_init();

		$this->theme = $this->themes()->get_current();
		do_action( 'usp_theme_init' );

		$this->office()->setup();
		do_action( 'usp_office_setup' );

		$this->setup_tabs();

		$this->customizer_init();

		if ( $this->is_request( 'frontend' ) ) {
			if ( usp_get_option_customizer( 'usp_bar_show', 1 ) || is_customize_preview() ) {
				$this->use_module( 'usp-bar' );
			}
		}

		if ( ! is_user_logged_in() ) {
			$this->use_module( 'loginform' );
		}

		if ( Ajax()->is_rest_request() ) {
			$this->use_module( 'forms' );
		}

		do_action( 'usp_init' );
	}

	function customizer_init() {
		require_once 'customizer/customizer.php';
	}

	function setup_tabs() {
		do_action( 'usp_init_tabs' );

		$this->tabs()->init_custom_tabs();

		$this->tabs()->order_tabs();

		do_action( 'usp_setup_tabs' );
	}

	function fields_init() {
		$this->fields = apply_filters( 'usp_fields', [
			'text'        => [
				'label' => __( 'Text', 'userspace' ),
				'class' => 'USP_Field_Text',
			],
			'time'        => [
				'label' => __( 'Time', 'userspace' ),
				'class' => 'USP_Field_Text',
			],
			'hidden'      => [
				'label' => __( 'Hidden field', 'userspace' ),
				'class' => 'USP_Field_Hidden',
			],
			'password'    => [
				'label' => __( 'Password', 'userspace' ),
				'class' => 'USP_Field_Text',
			],
			'url'         => [
				'label' => __( 'Url', 'userspace' ),
				'class' => 'USP_Field_Text',
			],
			'textarea'    => [
				'label' => __( 'Multiline text area', 'userspace' ),
				'class' => 'USP_Field_TextArea',
			],
			'select'      => [
				'label' => __( 'Select', 'userspace' ),
				'class' => 'USP_Field_Select',
			],
			'multiselect' => [
				'label' => __( 'Multi select', 'userspace' ),
				'class' => 'USP_Field_MultiSelect',
			],
			'switch'      => [
				'label' => __( 'Switch', 'userspace' ),
				'class' => 'USP_Field_Switch',
			],
			'checkbox'    => [
				'label' => __( 'Checkbox', 'userspace' ),
				'class' => 'USP_Field_Checkbox',
			],
			'radio'       => [
				'label' => __( 'Radio button', 'userspace' ),
				'class' => 'USP_Field_Radio',
			],
			'email'       => [
				'label' => __( 'E-mail', 'userspace' ),
				'class' => 'USP_Field_Text',
			],
			'tel'         => [
				'label' => __( 'Phone', 'userspace' ),
				'class' => 'USP_Field_Tel',
			],
			'number'      => [
				'label' => __( 'Number', 'userspace' ),
				'class' => 'USP_Field_Number',
			],
			'date'        => [
				'label' => __( 'Date', 'userspace' ),
				'class' => 'USP_Field_Date',
			],
			'agree'       => [
				'label' => __( 'Agreement', 'userspace' ),
				'class' => 'USP_Field_Agree',
			],
			'file'        => [
				'label' => __( 'File', 'userspace' ),
				'class' => 'USP_Field_File',
			],
			'dynamic'     => [
				'label' => __( 'Dynamic', 'userspace' ),
				'class' => 'USP_Field_Dynamic',
			],
			'runner'      => [
				'label' => __( 'Runner', 'userspace' ),
				'class' => 'USP_Field_Runner',
			],
			'range'       => [
				'label' => __( 'Range', 'userspace' ),
				'class' => 'USP_Field_Range',
			],
			'color'       => [
				'label' => __( 'Color', 'userspace' ),
				'class' => 'USP_Field_Color',
			],
			'custom'      => [
				'label' => __( 'Custom content', 'userspace' ),
				'class' => 'USP_Field_Custom',
			],
			'editor'      => [
				'label' => __( 'Text editor', 'userspace' ),
				'class' => 'USP_Field_Editor',
			],
			'uploader'    => [
				'label' => __( 'File uploader', 'userspace' ),
				'class' => 'USP_Field_Uploader',
			],
		] );
	}

	public function includes() {
		/*
		 * Here we will connect the files that are needed globally for the plugin
		 * The rest will be based on the corresponding functions
		 */
		require_once 'core/class-usp-module.php';
		require_once 'core/Attachments/OptAttachment.php';
		require_once 'core/Attachments/OptAttachments.php';
		require_once 'core/Query/QueryBuilder.php';
		require_once 'core/Query/DefaultTable/BlacklistQuery.php';
		require_once 'core/Query/DefaultTable/CommentsQuery.php';
		require_once 'core/Query/DefaultTable/PostsQuery.php';
		require_once 'core/Query/DefaultTable/PostsMetaQuery.php';
		require_once 'core/Query/DefaultTable/TempMediaQuery.php';
		require_once 'core/Query/DefaultTable/UserActionsQuery.php';
		require_once 'core/Query/DefaultTable/UsersMetaQuery.php';
		require_once 'core/Query/DefaultTable/UsersQuery.php';
		require_once 'core/Ajax.php';

		require_once 'core/Options.php';
		require_once 'core/Pager.php';
		require_once 'core/Users.php';
		require_once 'core/User.php';
		require_once 'core/Office.php';
		require_once 'core/UspWalker.php';
		require_once 'core/class-usp-includer.php';
		require_once 'core/Install.php';
		require_once 'core/Log.php';
		require_once 'core/Button.php';
		require_once 'core/Theme.php';
		require_once 'core/Themes.php';
		require_once 'core/Template.php';

		require_once 'functions/ajax.php';
		require_once 'functions/files.php';
		require_once 'functions/plugin-pages.php';
		require_once 'functions/enqueue-scripts.php';
		require_once 'functions/cron.php';
		require_once 'functions/currency.php';
		require_once 'functions/shortcodes.php';
		require_once 'functions/functions-access.php';
		require_once 'functions/functions-avatar.php';
		require_once 'functions/functions-media.php';
		require_once 'functions/functions-office.php';
		require_once 'functions/functions-options.php';
		require_once 'functions/functions-tabs.php';
		require_once 'functions/functions-user.php';
		require_once 'functions/functions-others.php';

		require_once 'functions/frontend.php';

		if ( $this->is_request( 'admin' ) ) {
			$this->admin_includes();
		}
	}

	function init_module( $module_id, $path, $parents = [] ) {
		$this->modules[ $module_id ] = new USP_Module( $path, $parents );
	}

	function use_module( $module_id ) {
		if ( $this->used_modules && in_array( $module_id, $this->used_modules ) ) {
			return;
		}

		$module = $this->modules[ $module_id ];

		if ( $module->parents ) {
			foreach ( $module->parents as $parent_id ) {
				$this->use_module( $parent_id );
			}
		}

		$this->modules[ $module_id ]->inc();

		$this->used_modules[] = $module_id;
	}

	private function init_modules() {
		$this->modules = [
			'loginform'       => new USP_Module( USP_PATH . 'modules/loginform/index.php', [ 'forms' ] ),
			'usp-bar'         => new USP_Module( USP_PATH . 'modules/usp-bar/index.php' ),
			'uploader'        => new USP_Module( USP_PATH . 'modules/uploader/index.php' ),
			'table'           => new USP_Module( USP_PATH . 'modules/table/index.php' ),
			'tabs'            => new USP_Module( USP_PATH . 'modules/tabs/index.php' ),
			'forms'           => new USP_Module( USP_PATH . 'modules/forms/index.php', [ 'fields' ] ),
			'fields'          => new USP_Module( USP_PATH . 'modules/fields/index.php', [ 'uploader' ] ),
			'fields-manager'  => new USP_Module( USP_PATH . 'modules/fields-manager/index.php', [ 'fields' ] ),
			'content-manager' => new USP_Module( USP_PATH . 'modules/content-manager/index.php', [ 'fields', 'table' ] ),
			'options-manager' => new USP_Module( USP_PATH . 'modules/options-manager/index.php', [ 'fields' ] ),
			'profile'         => new USP_Module( USP_PATH . 'modules/profile/index.php' ),
			'profile-fields'  => new USP_Module( USP_PATH . 'modules/profile-fields/index.php', [ 'fields' ] ),
			'users-list'      => new USP_Module( USP_PATH . 'modules/users-list/index.php', [ 'content-manager' ] ),
			'dropdown-menu'   => new USP_Module( USP_PATH . 'modules/usp-dropdown-menu/index.php' ),
		];
	}

	public function upload_dir() {
		if ( defined( 'MULTISITE' ) ) {
			$upload_dir = [
				'basedir' => WP_CONTENT_DIR . '/uploads',
				'baseurl' => WP_CONTENT_URL . '/uploads',
			];
		} else {
			$upload_dir = wp_upload_dir();
		}

		if ( is_ssl() ) {
			$upload_dir['baseurl'] = str_replace( 'http://', 'https://', $upload_dir['baseurl'] );
		}

		return apply_filters( 'usp_upload_dir', $upload_dir, $this );
	}

	public function office() {
		return Office::getInstance();
	}

	public function users() {
		return Users::getInstance();
	}

	public function user( $user_id = 0 ) {
		$user_id = $user_id ?: get_current_user_id();

		if ( ! $user_id ) {
			return null;
		}

		if ( $this->users()->isset( $user_id ) ) {
			return $this->users()->get( $user_id );
		}

		$user = new User( $user_id );

		$this->users()->add( $user );

		return $user;
	}

	public function profile_fields() {
		$this->use_module( 'profile-fields' );

		return new USP_Profile_Fields();
	}

	public function themes() {
		return new Themes();
	}

	public function tabs() {
		return USP_Tabs::instance();
	}

	public function template( $name, $file = false ) {
		return new Template( $name, $file );
	}

	public function theme() {
		return $this->theme;
	}

	function get_fields() {
		return $this->fields;
	}

	function get_used_modules() {
		return $this->used_modules;
	}

	function options() {
		return Options::getInstance();
	}

}

function USP() {
	return UserSpace::getInstance();
}

$GLOBALS['userspace'] = USP();

USP()->use_module( 'tabs' );
USP()->use_module( 'profile' );
USP()->use_module( 'dropdown-menu' );
