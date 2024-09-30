<?php

namespace USP;

use USP\Core\Module\Fields\Type\FieldAgree;
use USP\Core\Module\Fields\Type\FieldCheckbox;
use USP\Core\Module\Fields\Type\FieldColor;
use USP\Core\Module\Fields\Type\FieldCustom;
use USP\Core\Module\Fields\Type\FieldDate;
use USP\Core\Module\Fields\Type\FieldDynamic;
use USP\Core\Module\Fields\Type\FieldEditor;
use USP\Core\Module\Fields\Type\FieldFile;
use USP\Core\Module\Fields\Type\FieldHidden;
use USP\Core\Module\Fields\Type\FieldMultiSelect;
use USP\Core\Module\Fields\Type\FieldNumber;
use USP\Core\Module\Fields\Type\FieldPhone;
use USP\Core\Module\Fields\Type\FieldRadio;
use USP\Core\Module\Fields\Type\FieldRange;
use USP\Core\Module\Fields\Type\FieldRunner;
use USP\Core\Module\Fields\Type\FieldSelect;
use USP\Core\Module\Fields\Type\FieldSwitch;
use USP\Core\Module\Fields\Type\FieldText;
use USP\Core\Module\Fields\Type\FieldTextArea;
use USP\Core\Module\Fields\Type\FieldUploader;
use USP\Core\Module\ProfileFields\ProfileFields;
use USP\Core\Module\Tabs\Tabs;
use USP\Core\Office;
use USP\Core\Options;
use USP\Core\Template;
use USP\Core\Theme;
use USP\Core\Themes;
use USP\Core\User;
use USP\Core\Users;

final class UserSpace {

	private string $version = '1.0.0';
	private ?Theme $theme = null;
	private array $fields = [];
	private static ?UserSpace $instance = null;

	public static function getInstance(): ?UserSpace {
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

	private function define_constants(): void {
		global $wpdb;

		$upload_dir = $this->upload_dir();

		$this->define( 'USP_VERSION', $this->version );

		$this->define( 'USP_URL', plugins_url( '/', dirname(__FILE__) ) );
		$this->define( 'USP_PREF', $wpdb->base_prefix . 'usp_' );

		$this->define( 'USP_PATH', plugin_dir_path( dirname(__FILE__) ) );
		$this->define( 'USP_UPLOAD_PATH', $upload_dir['basedir'] . '/usp-uploads/' );
		$this->define( 'USP_UPLOAD_URL', $upload_dir['baseurl'] . '/usp-uploads/' );

		$this->define( 'USP_TAKEPATH', WP_CONTENT_DIR . '/userspace/' );
	}

	private function define( string $name, mixed $value ): void {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	private function load_options(): void {
		Options::getInstance();
	}

	private function init_hooks(): void {
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

	public function update_user_activity(): void {
		if ( ! is_user_logged_in() ) {
			return;
		}

		usp_user_update_activity();
	}

	public function register_theme_header( array $extra_context_headers ): array {
		$extra_context_headers['UserSpaceTheme'] = 'UserSpaceTheme';

		return $extra_context_headers;
	}

	/*
	 * Find out the type of request
	 */
	private function is_request( string $type ): bool {
		return match ( $type ) {
			'admin' => is_admin(),
			'ajax' => defined( 'DOING_AJAX' ),
			'cron' => defined( 'DOING_CRON' ),
			'frontend' => ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' ),
			default => false,
		};
	}

	//all files for the admin panel
	public function admin_includes(): void {
		require_once USP_PATH . '/admin/index.php';
	}

	public function userspace_office_load(): void {
	}

	public function init(): void {
		do_action( 'usp_before_init' );

		$this->fields_init();

		$this->theme = $this->themes()->get_current();
		do_action( 'usp_theme_init' );

		$this->office()->setup();
		do_action( 'usp_office_setup' );

		$this->setup_tabs();

		$this->customizer_init();

		do_action( 'usp_init' );
	}

	public function customizer_init(): void {
		require_once USP_PATH . '/customizer/customizer.php';
	}

	public function setup_tabs(): void {
		do_action( 'usp_init_tabs' );

		$this->tabs()->init_custom_tabs();

		$this->tabs()->order_tabs();

		do_action( 'usp_setup_tabs' );
	}

	public function fields_init(): void {
		$this->fields = apply_filters( 'usp_fields', [
			'text'        => [
				'label' => __( 'Text', 'userspace' ),
				'class' => FieldText::class,
			],
			'time'        => [
				'label' => __( 'Time', 'userspace' ),
				'class' => FieldText::class,
			],
			'hidden'      => [
				'label' => __( 'Hidden field', 'userspace' ),
				'class' => FieldHidden::class,
			],
			'password'    => [
				'label' => __( 'Password', 'userspace' ),
				'class' => FieldText::class,
			],
			'url'         => [
				'label' => __( 'Url', 'userspace' ),
				'class' => FieldText::class,
			],
			'textarea'    => [
				'label' => __( 'Multiline text area', 'userspace' ),
				'class' => FieldTextArea::class,
			],
			'select'      => [
				'label' => __( 'Select', 'userspace' ),
				'class' => FieldSelect::class,
			],
			'multiselect' => [
				'label' => __( 'Multi select', 'userspace' ),
				'class' => FieldMultiSelect::class,
			],
			'switch'      => [
				'label' => __( 'Switch', 'userspace' ),
				'class' => FieldSwitch::class,
			],
			'checkbox'    => [
				'label' => __( 'Checkbox', 'userspace' ),
				'class' => FieldCheckbox::class,
			],
			'radio'       => [
				'label' => __( 'Radio button', 'userspace' ),
				'class' => FieldRadio::class,
			],
			'email'       => [
				'label' => __( 'E-mail', 'userspace' ),
				'class' => FieldText::class,
			],
			'tel'         => [
				'label' => __( 'Phone', 'userspace' ),
				'class' => FieldPhone::class,
			],
			'number'      => [
				'label' => __( 'Number', 'userspace' ),
				'class' => FieldNumber::class,
			],
			'date'        => [
				'label' => __( 'Date', 'userspace' ),
				'class' => FieldDate::class,
			],
			'agree'       => [
				'label' => __( 'Agreement', 'userspace' ),
				'class' => FieldAgree::class,
			],
			'file'        => [
				'label' => __( 'File', 'userspace' ),
				'class' => FieldFile::class,
			],
			'dynamic'     => [
				'label' => __( 'Dynamic', 'userspace' ),
				'class' => FieldDynamic::class,
			],
			'runner'      => [
				'label' => __( 'Runner', 'userspace' ),
				'class' => FieldRunner::class,
			],
			'range'       => [
				'label' => __( 'Range', 'userspace' ),
				'class' => FieldRange::class,
			],
			'color'       => [
				'label' => __( 'Color', 'userspace' ),
				'class' => FieldColor::class,
			],
			'custom'      => [
				'label' => __( 'Custom content', 'userspace' ),
				'class' => FieldCustom::class,
			],
			'editor'      => [
				'label' => __( 'Text editor', 'userspace' ),
				'class' => FieldEditor::class,
			],
			'uploader'    => [
				'label' => __( 'File uploader', 'userspace' ),
				'class' => FieldUploader::class,
			],
		] );
	}

	public function includes(): void {
		/*
		 * Here we will connect the files that are needed globally for the plugin
		 * The rest will be based on the corresponding functions
		 */

		require_once USP_PATH . '/functions/ajax.php';
		require_once USP_PATH . '/functions/files.php';
		require_once USP_PATH . '/functions/plugin-pages.php';
		require_once USP_PATH . '/functions/enqueue-scripts.php';
		require_once USP_PATH . '/functions/cron.php';
		require_once USP_PATH . '/functions/shortcodes.php';
		require_once USP_PATH . '/functions/functions-access.php';
		require_once USP_PATH . '/functions/functions-avatar.php';
		require_once USP_PATH . '/functions/functions-media.php';
		require_once USP_PATH . '/functions/functions-office.php';
		require_once USP_PATH . '/functions/functions-options.php';
		require_once USP_PATH . '/functions/functions-tabs.php';
		require_once USP_PATH . '/functions/functions-user.php';
		require_once USP_PATH . '/functions/functions-others.php';
		require_once USP_PATH . '/functions/frontend.php';

		if ( $this->is_request( 'admin' ) ) {
			$this->admin_includes();
		}
	}

	private function init_modules(): void {
		(new \USP\Admin\OptionsManager\Initializer())->init();
		(new \USP\Core\Module\ContentManager\Initializer())->init();
		(new \USP\Core\Module\Fields\Initializer())->init();
		(new \USP\Core\Module\FieldsManager\Initializer())->init();
		(new \USP\Core\Module\Forms\Initializer())->init();
		(new \USP\Core\Module\Profile\Initializer())->init();
		(new \USP\Core\Module\Table\Initializer())->init();
		(new \USP\Core\Module\Uploader\Initializer())->init();
		(new \USP\Core\Module\DropdownMenu\Initializer())->init();
	}

	public function upload_dir(): array {
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

	public function office(): ?Office {
		return Office::getInstance();
	}

	public function users(): ?Users {
		return Users::getInstance();
	}

	public function user( int $user_id = 0 ): ?User {
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

	public function profile_fields(): ProfileFields {
		return new ProfileFields();
	}

	public function themes(): Themes {
		return new Themes();
	}

	public function tabs(): ?Tabs {
		return Tabs::instance();
	}

	public function template( string $name, string $file = null ) {
		return new Template( $name, $file );
	}

	public function theme(): ?Theme {
		return $this->theme;
	}

	public function get_fields(): array {
		return $this->fields;
	}

	public function options(): ?Options {
		return Options::getInstance();
	}

}