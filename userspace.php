<?php
/*
  Plugin Name: UserSpace
  Plugin URI: http://user-space.com/
  Description: Login & registration form, profile fields, front-end profile, user account and core for wordpress membership.
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
	private $fields = array();
	private $modules = array();
	private $vars = [];
	private $varnames = array(
		'member' => 'user'
	);
	private $used_modules = array();
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
		USP_Options::getInstance();
	}

	function set_rewrite_rules() {
		global $wp_rewrite;

		if ( ! $page_id = $this->options()->get( 'account_page' ) ) {
			return false;
		}

		$page      = get_post( $page_id );
		$slugmatch = $page->post_name;
		if ( $wp_rewrite->using_index_permalinks() && $wp_rewrite->root == 'index.php/' ) {
			$slugmatch = 'index.php/' . $slugmatch;
		}

		add_rewrite_rule( $slugmatch . '/([^/]+)/?$', 'index.php?pagename=' . $page->post_name . '&' . $this->varnames['member'] . '=$matches[1]', 'top' );

	}


	function set_query_vars( $vars ) {
		$vars[] = $this->varnames['member'];
		return $vars;
	}

	private function init_hooks() {

		register_activation_hook( __FILE__, array( 'USP_Install', 'install' ) );

		add_action( 'plugins_loaded', [ $this, 'parse_vars' ] );

		add_action( 'init', array( $this, 'init' ), 0 );

		add_action( 'usp_area_before', array( $this, 'userspace_office_load' ) );

		add_filter( 'query_vars', array( $this, 'set_query_vars' ) );

		/**
		 * Register our extra header for themes
		 *
		 * @since 1.0
		 */
		add_filter( 'extra_plugin_headers', array( $this, 'register_theme_header' ) );

		if ( ! is_admin() ) {
			add_action( 'usp_enqueue_scripts', 'usp_core_resources', 1 );
			add_action( 'usp_enqueue_scripts', 'usp_frontend_scripts', 1 );
			add_action( 'wp_head', [ $this, 'update_user_activity' ], 10 );
		}

	}

	function parse_vars() {

		if ( ! $page_id = $this->options()->get( 'account_page' ) ) {
			return false;
		}

		if ( '' !== get_site_option( 'permalink_structure' ) ) {

			$slugmatch = get_post( $page_id )->post_name;

			$url = parse_url( $_SERVER['REQUEST_URI'] );

			preg_match( '/\/' . $slugmatch . '\/([^\/]+)/', $url['path'], $matches );

			if ( ! empty( $matches[1] ) ) {
				$member = $matches[1];
			}

			if ( ! is_numeric( $member ) && $user = get_user_by( 'slug', $member ) ) {
				$member = $user->ID;
			}

		} else {
			$member = $_GET[ $this->varnames['member'] ];
		}

		$this->vars = array(
			'member' => $member
		);

	}

	function get_var($var_key){
		return !empty($this->vars[$var_key])? $this->vars[$var_key]: false;
	}

	function update_user_activity() {
		$this->user()->update_activity();
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

		$this->set_rewrite_rules();

		$this->fields_init();

		$this->theme = $this->themes()->get_current();
		do_action('usp_theme_init');

		$this->office()->setup( $this->vars['member'] );
		do_action('usp_office_setup');

		$this->setup_tabs();

		if ( $this->is_request( 'frontend' ) ) {
			if ( $this->options()->get( 'usp_bar_show' ) ) {
				$this->use_module( 'usp-bar' );
			}
		}

		if ( ! is_user_logged_in() ) {
			$this->use_module( 'loginform' );
		}

		if ( USP_Ajax()->is_rest_request() ) {
			$this->use_module( 'forms' );
		}

		do_action( 'usp_init' );
	}

	function setup_tabs() {
		do_action( 'usp_init_tabs' );

		$this->tabs()->init_custom_tabs();

		$this->tabs()->order_tabs();

		do_action( 'usp_setup_tabs' );
	}

	function fields_init() {

		$this->fields = apply_filters( 'usp_fields', array(
			'text'        => array(
				'label' => __( 'Text', 'userspace' ),
				'class' => 'USP_Field_Text'
			),
			'time'        => array(
				'label' => __( 'Time', 'userspace' ),
				'class' => 'USP_Field_Text'
			),
			'hidden'      => array(
				'label' => __( 'Hidden field', 'userspace' ),
				'class' => 'USP_Field_Hidden'
			),
			'password'    => array(
				'label' => __( 'Password', 'userspace' ),
				'class' => 'USP_Field_Text'
			),
			'url'         => array(
				'label' => __( 'Url', 'userspace' ),
				'class' => 'USP_Field_Text'
			),
			'textarea'    => array(
				'label' => __( 'Multiline text area', 'userspace' ),
				'class' => 'USP_Field_TextArea'
			),
			'select'      => array(
				'label' => __( 'Select', 'userspace' ),
				'class' => 'USP_Field_Select'
			),
			'multiselect' => array(
				'label' => __( 'Multi select', 'userspace' ),
				'class' => 'USP_Field_MultiSelect'
			),
			'switch'      => array(
				'label' => __( 'Switch', 'userspace' ),
				'class' => 'USP_Field_Switch'
			),
			'checkbox'    => array(
				'label' => __( 'Checkbox', 'userspace' ),
				'class' => 'USP_Field_Checkbox'
			),
			'radio'       => array(
				'label' => __( 'Radio button', 'userspace' ),
				'class' => 'USP_Field_Radio'
			),
			'email'       => array(
				'label' => __( 'E-mail', 'userspace' ),
				'class' => 'USP_Field_Text'
			),
			'tel'         => array(
				'label' => __( 'Phone', 'userspace' ),
				'class' => 'USP_Field_Tel'
			),
			'number'      => array(
				'label' => __( 'Number', 'userspace' ),
				'class' => 'USP_Field_Number'
			),
			'date'        => array(
				'label' => __( 'Date', 'userspace' ),
				'class' => 'USP_Field_Date'
			),
			'agree'       => array(
				'label' => __( 'Agreement', 'userspace' ),
				'class' => 'USP_Field_Agree'
			),
			'file'        => array(
				'label' => __( 'File', 'userspace' ),
				'class' => 'USP_Field_File'
			),
			'dynamic'     => array(
				'label' => __( 'Dynamic', 'userspace' ),
				'class' => 'USP_Field_Dynamic'
			),
			'runner'      => array(
				'label' => __( 'Runner', 'userspace' ),
				'class' => 'USP_Field_Runner'
			),
			'range'       => array(
				'label' => __( 'Range', 'userspace' ),
				'class' => 'USP_Field_Range'
			),
			'color'       => array(
				'label' => __( 'Color', 'userspace' ),
				'class' => 'USP_Field_Color'
			),
			'custom'      => array(
				'label' => __( 'Custom content', 'userspace' ),
				'class' => 'USP_Field_Custom'
			),
			'editor'      => array(
				'label' => __( 'Text editor', 'userspace' ),
				'class' => 'USP_Field_Editor'
			),
			'uploader'    => array(
				'label' => __( 'File uploader', 'userspace' ),
				'class' => 'USP_Field_Uploader'
			)
		) );
	}

	public function includes() {
		/*
		 * Here we will connect the files that are needed globally for the plugin
		 * The rest will be based on the corresponding functions
		 */
		require_once 'classes/class-usp-module.php';

		require_once 'classes/query/class-usp-query.php';
		require_once 'classes/class-usp-query-tables.php';
		require_once 'classes/class-usp-cache.php';
		require_once 'classes/class-usp-ajax.php';

		require_once 'classes/class-usp-options.php';
		require_once 'classes/class-usp-pager.php';
		require_once 'classes/class-usp-user.php';
		require_once 'classes/class-usp-office.php';
		require_once 'classes/class-usp-walker.php';
		require_once 'classes/class-usp-includer.php';
		require_once 'classes/class-usp-install.php';
		require_once 'classes/class-usp-log.php';
		require_once 'classes/class-usp-button.php';
		require_once 'classes/class-usp-theme.php';
		require_once 'classes/class-usp-themes.php';
		require_once 'classes/class-usp-template.php';

		require_once 'functions/ajax.php';
		require_once 'functions/files.php';
		require_once 'functions/plugin-pages.php';
		require_once 'functions/enqueue-scripts.php';
		require_once 'functions/cron.php';
		require_once 'functions/currency.php';
		require_once 'functions/shortcodes.php';
		require_once 'functions/functions-access.php';
		require_once 'functions/functions-avatar.php';
		require_once 'functions/functions-cache.php';
		require_once 'functions/functions-media.php';
		require_once 'functions/functions-office.php';
		require_once 'functions/functions-options.php';
		require_once 'functions/functions-tabs.php';
		require_once 'functions/functions-user.php';
		require_once 'functions/functions-others.php';

		require_once 'functions/frontend.php';
		require_once 'functions/widgets.php';

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
			'gallery'         => new USP_Module( USP_PATH . 'modules/gallery/index.php' ),
			'table'           => new USP_Module( USP_PATH . 'modules/table/index.php' ),
			'tabs'            => new USP_Module( USP_PATH . 'modules/tabs/index.php' ),
			'forms'           => new USP_Module( USP_PATH . 'modules/forms/index.php', [ 'fields' ] ),
			'fields'          => new USP_Module( USP_PATH . 'modules/fields/index.php', [ 'uploader' ] ),
			'fields-manager'  => new USP_Module( USP_PATH . 'modules/fields-manager/index.php', [ 'fields' ] ),
			'content-manager' => new USP_Module( USP_PATH . 'modules/content-manager/index.php', [
				'fields',
				'table'
			] ),
			'options-manager' => new USP_Module( USP_PATH . 'modules/options-manager/index.php', [ 'fields' ] ),
			'profile'         => new USP_Module( USP_PATH . 'modules/profile/index.php' ),
			'users-list'      => new USP_Module( USP_PATH . 'modules/users-list/index.php' ),
		];
	}

	public function upload_dir() {

		if ( defined( 'MULTISITE' ) ) {
			$upload_dir = array(
				'basedir' => WP_CONTENT_DIR . '/uploads',
				'baseurl' => WP_CONTENT_URL . '/uploads'
			);
		} else {
			$upload_dir = wp_upload_dir();
		}

		if ( is_ssl() ) {
			$upload_dir['baseurl'] = str_replace( 'http://', 'https://', $upload_dir['baseurl'] );
		}

		return apply_filters( 'usp_upload_dir', $upload_dir, $this );
	}

	public function office() {
		return USP_Office::getInstance();
	}

	function user( $user_id = false ) {
		return new USP_User( $user_id );
	}

	public function themes() {
		return new USP_Themes();
	}

	public function tabs() {
		return USP_Tabs::instance();
	}

	public function template( $name, $file = false ) {
		return new USP_Template( $name, $file );
	}

	public function theme() {
		return $this->theme;
	}

	function get_fields(){
		return $this->fields;
	}

	function get_used_modules(){
		return $this->used_modules;
	}

	function options() {
		return USP_Options::getInstance();
	}

}

function USP() {
	return UserSpace::getInstance();
}

$GLOBALS['userspace'] = USP();

USP()->use_module( 'tabs' );
USP()->use_module( 'profile' );
