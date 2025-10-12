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
    private array $fields = [];
	private ?Theme $theme = null;

	private Users $users;
	private Office $office;
	private Tabs $tabs;
	private Themes $themes;
	private Options $options;

	public function __construct(
		Users $users,
		Office $office,
		Tabs $tabs,
		Themes $themes,
		Options $options
	) {
		$this->users   = $users;
		$this->office  = $office;
		$this->tabs    = $tabs;
		$this->themes  = $themes;
		$this->options = $options;
	}

    /**
     * Основной метод для запуска плагина.
     */
    public function run(): void
    {
        $this->define_constants();
        $this->includes();
        $this->init_hooks();
        $this->init_modules();
    }

    private function define_constants(): void
    {
        global $wpdb;

        $upload_dir = $this->upload_dir();

        $this->define('USP_VERSION', $this->version);
        $this->define('USP_URL', plugins_url('/', dirname(__FILE__)));
        $this->define('USP_PREF', $wpdb->base_prefix . 'usp_');
        $this->define('USP_PATH', plugin_dir_path(dirname(__FILE__)));
        $this->define('USP_UPLOAD_PATH', $upload_dir['basedir'] . '/usp-uploads/');
        $this->define('USP_UPLOAD_URL', $upload_dir['baseurl'] . '/usp-uploads/');
        $this->define('USP_TAKEPATH', WP_CONTENT_DIR . '/userspace/');
    }

    private function define(string $name, $value): void
    {
        if (!defined($name)) {
            define($name, $value);
        }
    }

    private function init_hooks(): void
    {
        add_action('init', [$this, 'init'], 0);
        add_action('usp_area_before', [$this, 'userspace_office_load']);
        add_filter('extra_plugin_headers', [$this, 'register_theme_header']);

        if (!$this->is_request('admin')) {
            add_action('usp_enqueue_scripts', 'usp_core_resources', 1);
            add_action('usp_enqueue_scripts', 'usp_frontend_scripts', 1);
            add_action('wp_head', [$this, 'update_user_activity'], 10);
        }
    }

    public function update_user_activity(): void
    {
        if (!is_user_logged_in()) {
            return;
        }
        usp_user_update_activity();
    }

    public function register_theme_header(array $extra_context_headers): array
    {
        $extra_context_headers['UserSpaceTheme'] = 'UserSpaceTheme';
        return $extra_context_headers;
    }

    private function is_request(string $type): bool
    {
        switch ($type) {
            case 'admin':
                return is_admin();
            case 'ajax':
                return defined('DOING_AJAX');
            case 'cron':
                return defined('DOING_CRON');
            case 'frontend':
                return (!is_admin() || defined('DOING_AJAX')) && !defined('DOING_CRON');
            default:
                return false;
        }
    }

    public function userspace_office_load(): void
    {
        // Логика загрузки офиса
    }

    public function init(): void
    {
        do_action('usp_before_init');

        $this->fields_init();

        $this->theme = $this->themes->get_current();
        do_action('usp_theme_init');

        $this->office()->setup();
        do_action('usp_office_setup');

        $this->setup_tabs();
        $this->customizer_init();

        do_action('usp_init');
    }

    public function customizer_init(): void
    {
        require_once USP_PATH . '/customizer/customizer.php';
    }

    public function setup_tabs(): void
    {
        do_action('usp_init_tabs');
        $this->tabs->init_custom_tabs();
        $this->tabs->order_tabs();
        do_action('usp_setup_tabs');
    }

    public function fields_init(): void
    {
        $this->fields = apply_filters('usp_fields', [
            'text' => ['label' => __('Text', 'userspace'), 'class' => FieldText::class],
            'time' => ['label' => __('Time', 'userspace'), 'class' => FieldText::class],
            'hidden' => ['label' => __('Hidden field', 'userspace'), 'class' => FieldHidden::class],
            'password' => ['label' => __('Password', 'userspace'), 'class' => FieldText::class],
            'url' => ['label' => __('Url', 'userspace'), 'class' => FieldText::class],
            'textarea' => ['label' => __('Multiline text area', 'userspace'), 'class' => FieldTextArea::class],
            'select' => ['label' => __('Select', 'userspace'), 'class' => FieldSelect::class],
            'multiselect' => ['label' => __('Multi select', 'userspace'), 'class' => FieldMultiSelect::class],
            'switch' => ['label' => __('Switch', 'userspace'), 'class' => FieldSwitch::class],
            'checkbox' => ['label' => __('Checkbox', 'userspace'), 'class' => FieldCheckbox::class],
            'radio' => ['label' => __('Radio button', 'userspace'), 'class' => FieldRadio::class],
            'email' => ['label' => __('E-mail', 'userspace'), 'class' => FieldText::class],
            'tel' => ['label' => __('Phone', 'userspace'), 'class' => FieldPhone::class],
            'number' => ['label' => __('Number', 'userspace'), 'class' => FieldNumber::class],
            'date' => ['label' => __('Date', 'userspace'), 'class' => FieldDate::class],
            'agree' => ['label' => __('Agreement', 'userspace'), 'class' => FieldAgree::class],
            'file' => ['label' => __('File', 'userspace'), 'class' => FieldFile::class],
            'dynamic' => ['label' => __('Dynamic', 'userspace'), 'class' => FieldDynamic::class],
            'runner' => ['label' => __('Runner', 'userspace'), 'class' => FieldRunner::class],
            'range' => ['label' => __('Range', 'userspace'), 'class' => FieldRange::class],
            'color' => ['label' => __('Color', 'userspace'), 'class' => FieldColor::class],
            'custom' => ['label' => __('Custom content', 'userspace'), 'class' => FieldCustom::class],
            'editor' => ['label' => __('Text editor', 'userspace'), 'class' => FieldEditor::class],
            'uploader' => ['label' => __('File uploader', 'userspace'), 'class' => FieldUploader::class],
        ]);
    }

    /**
     * Подключает необходимые файлы.
     * Рекомендуется перенести эти функции в классы-хелперы и использовать автозагрузку.
     */
    public function includes(): void
    {
        $base_path = USP_PATH . '/functions/';
        $files = [
            'ajax.php', 'files.php', 'plugin-pages.php', 'enqueue-scripts.php', 'cron.php',
            'shortcodes.php', 'functions-access.php', 'functions-avatar.php', 'functions-media.php',
            'functions-office.php', 'functions-options.php', 'functions-tabs.php', 'functions-user.php',
            'functions-others.php', 'frontend.php'
        ];

        foreach ($files as $file) {
            require_once $base_path . $file;
        }

        if ($this->is_request('admin')) {
            require_once USP_PATH . '/admin/index.php';
        }
    }

    private function init_modules(): void
    {
        $this->options(); // Загрузка опций

        $initializers = [
            \USP\Admin\OptionsManager\Initializer::class,
            \USP\Core\Module\ContentManager\Initializer::class,
            \USP\Core\Module\Fields\Initializer::class,
            \USP\Core\Module\FieldsManager\Initializer::class,
            \USP\Core\Module\Forms\Initializer::class,
            \USP\Core\Module\Profile\Initializer::class,
            \USP\Core\Module\Table\Initializer::class,
            \USP\Core\Module\Uploader\Initializer::class,
            \USP\Core\Module\DropdownMenu\Initializer::class,
        ];

        foreach ($initializers as $initializer) {
            (new $initializer())->init();
        }
    }

    public function upload_dir(): array
    {
        $upload_dir = defined('MULTISITE')
            ? ['basedir' => WP_CONTENT_DIR . '/uploads', 'baseurl' => WP_CONTENT_URL . '/uploads']
            : wp_upload_dir();

        if (is_ssl()) {
            $upload_dir['baseurl'] = str_replace('http://', 'https://', $upload_dir['baseurl']);
        }

        return apply_filters('usp_upload_dir', $upload_dir, $this);
    }

    public function office(): Office
    {
        return $this->office;
    }

    public function users(): Users
    {
        return $this->users;
    }

    public function user(int $user_id = 0): ?User
    {
        $user_id = $user_id ?: get_current_user_id();
        if (!$user_id) {
            return null;
        }

        if ($this->users->isset($user_id)) {
            return $this->users->get($user_id);
        }

        $user = new User($user_id);
        $this->users->add($user);

        return $user;
    }

    public function profile_fields(): ProfileFields
    {
        return new ProfileFields();
    }

    public function themes(): Themes
    {
        return $this->themes;
    }

    public function tabs(): Tabs
    {
        return $this->tabs;
    }

    public function template(string $name, string $file = null): Template
    {
        return new Template($name, $file);
    }

    public function theme(): ?Theme
    {
        return $this->theme;
    }

    public function get_fields(): array
    {
        return $this->fields;
    }

    public function options(): Options
    {
        return $this->options;
    }
}