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

    public $version      = '1.0.0';
    public $theme        = null;
    public $fields       = array();
    public $tabs         = array();
    public $modules      = array();
    public $used_modules = array();

    public static function getInstance() {
        static $instance;

        if ( null === $instance ) {
            $instance = new self();
        }

        return $instance;
    }

    private function __construct() {
        static $hasInstance = false;

        if ( $hasInstance ) {
            return;
        }

        $this->define_constants(); //Определяем константы.
        $this->includes(); //Подключаем все нужные файлы с функциями и классами
        $this->init_modules(); //Определяем модули.
        $this->init_hooks(); //Тут все наши хуки

        $this->use_module( 'tabs' );
        $this->use_module( 'forms' );
        $this->use_module( 'table' );
        $this->use_module( 'profile' );

        $this->init_theme();

        do_action( 'usp_loaded' ); //Оставляем кручёк

        $hasInstance = true;
    }

    public function __clone() {
        _doing_it_wrong( __FUNCTION__, __( 'Are you cheating, bastard?', 'userspace' ), $this->version );
    }

    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, __( 'Are you cheating, bastard?', 'userspace' ), $this->version );
    }

    private function init_modules() {

        $this->modules = [
            'loginform'       => new USP_Module( USP_PATH . 'modules/loginform/index.php', [ 'forms' ] ),
            'recallbar'       => new USP_Module( USP_PATH . 'modules/recallbar/index.php' ),
            'uploader'        => new USP_Module( USP_PATH . 'modules/uploader/index.php' ),
            'gallery'         => new USP_Module( USP_PATH . 'modules/gallery/index.php' ),
            'table'           => new USP_Module( USP_PATH . 'modules/table/index.php' ),
            'tabs'            => new USP_Module( USP_PATH . 'modules/tabs/index.php' ),
            'forms'           => new USP_Module( USP_PATH . 'modules/forms/index.php', [ 'fields' ] ),
            'fields'          => new USP_Module( USP_PATH . 'modules/fields/index.php', [ 'uploader' ] ),
            'fields-manager'  => new USP_Module( USP_PATH . 'modules/fields-manager/index.php', [ 'fields' ] ),
            'content-manager' => new USP_Module( USP_PATH . 'modules/content-manager/index.php', [ 'fields', 'table' ] ),
            'options-manager' => new USP_Module( USP_PATH . 'modules/options-manager/index.php', [ 'fields' ] ),
            'profile'         => new USP_Module( USP_PATH . 'modules/profile/index.php', [ 'forms' ] ),
        ];
    }

    function init_module( $module_id, $path, $parents = [] ) {
        $this->modules[$module_id] = new USP_Module( $path, $parents );
    }

    function use_module( $module_id ) {

        if ( $this->used_modules && in_array( $module_id, $this->used_modules ) )
            return;

        $module = $this->modules[$module_id];

        if ( $module->parents ) {
            foreach ( $module->parents as $parent_id ) {
                $this->use_module( $parent_id );
            }
        }

        $this->modules[$module_id]->inc();

        $this->used_modules[] = $module_id;
    }

    private function init_hooks() {

        register_activation_hook( __FILE__, array( 'USP_Install', 'install' ) );

        add_action( 'wp_loaded', array( $this, 'setup_tabs' ), 10 );

        add_action( 'init', array( $this, 'init' ), 0 );

        if ( ! is_admin() ) {
            add_action( 'usp_enqueue_scripts', 'usp_frontend_scripts', 1 );
            add_action( 'wp_head', 'usp_update_timeaction_user', 10 );
        }
    }

    private function define_constants() {
        global $wpdb;

        $upload_dir = $this->upload_dir();

        $this->define( 'USP_VERSION', $this->version );

        $this->define( 'USP_URL', $this->plugin_url() . '/' );
        $this->define( 'USP_PREF', $wpdb->base_prefix . 'usp_' );

        $this->define( 'USP_PATH', trailingslashit( $this->plugin_path() ) );
        $this->define( 'USP_UPLOAD_PATH', $upload_dir['basedir'] . '/usp-uploads/' );
        $this->define( 'USP_UPLOAD_URL', $upload_dir['baseurl'] . '/usp-uploads/' );

        $this->define( 'USP_TAKEPATH', WP_CONTENT_DIR . '/userspace/' );
    }

    private function define( $name, $value ) {
        if ( ! defined( $name ) ) {
            define( $name, $value );
        }
    }

    /*
     * Узнаём тип запроса
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

    function init_theme() {

        $this->theme = $this->themes()->get_current();

        do_action( 'usp_init_theme' );
    }

    function themes() {
        $themes = new USP_Themes();
        return $themes;
    }

    function tabs() {
        return USP_Tabs::instance();
    }

    public function includes() {
        /*
         * Здесь подключим те фалы которые нужны глобально для плагина
         * Остальные распихаем по соответсвующим функциям
         */
        require_once 'classes/class-usp-module.php';

        require_once 'classes/query/class-usp-query.php';
        require_once 'classes/query/class-rq.php';

        require_once 'classes/class-usp-query-tables.php';
        require_once 'classes/class-usp-cache.php';
        require_once 'classes/class-usp-ajax.php';

        /* deprecated, use class-usp-pager */
        require_once 'classes/class-usp-pagenavi.php';

        require_once 'classes/class-usp-pager.php';
        require_once 'classes/class-usp-user.php';
        require_once 'classes/class-usp-walker.php';
        require_once 'classes/class-usp-includer.php';
        require_once 'classes/class-usp-install.php';
        require_once 'classes/class-usp-log.php';
        require_once 'classes/class-usp-button.php';
        require_once 'classes/class-usp-theme.php';
        require_once 'classes/class-usp-themes.php';
        require_once 'classes/class-usp-template.php';

        require_once 'functions/activate.php';
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

        if ( $this->is_request( 'ajax' ) ) {
            $this->ajax_includes();
        }

        if ( $this->is_request( 'frontend' ) ) {
            $this->frontend_includes();
        }
    }

    /*
     * Сюда складываем все файлы для админки
     */
    public function admin_includes() {
        require_once 'admin/index.php';
    }

    /*
     * Сюда складываем все файлы AJAX
     */
    public function ajax_includes() {

    }

    /*
     * Сюда складываем все файлы для фронт-энда
     */
    public function frontend_includes() {

    }

    public function init() {
        global $user_ID;

        do_action( 'usp_before_init' );

        $this->fields_init();

        if ( $this->is_request( 'frontend' ) ) {

            if ( usp_get_option( 'view_recallbar' ) ) {
                $this->use_module( 'recallbar' );
            }

            $this->init_frontend_globals();
        }

        if ( ! usp_get_option( 'security-key' ) ) {
            usp_update_option( 'security-key', wp_generate_password( 20, false ) );
        }

        if ( ! $user_ID )
            $this->use_module( 'loginform' );

        do_action( 'usp_init' );
    }

    function setup_tabs() {

        do_action( 'usp_init_tabs' );

        $this->tabs()->init_custom_tabs();

        $this->tabs()->order_tabs();

        do_action( 'usp_setup_tabs' );
    }

    function init_frontend_globals() {
        global $wpdb, $user_LK, $usp_userlk_action, $user_ID, $usp_office, $usp_user_URL, $usp_current_action, $wp_rewrite;

        if ( $user_ID ) {
            $usp_user_URL       = usp_get_user_url( $user_ID );
            $usp_current_action = usp_get_time_user_action( $user_ID );
        }

        $user_LK = 0;

        //если вывод ЛК через шорткод
        if ( usp_get_option( 'view_user_lk_usp' ) == 1 ) {

            $get     = usp_get_option( 'link_user_lk_usp', 'user' );
            $user_LK = (isset( $_GET[$get] )) ? intval( $_GET[$get] ) : false;

            if ( ! $user_LK ) {
                $post_id = url_to_postid( $_SERVER['REQUEST_URI'] );
                if ( usp_get_option( 'lk_page_usp' ) == $post_id ) {
                    $user_LK = $user_ID;
                }
            }
        } else { //если ЛК выводим через author.php
            if ( '' == get_site_option( 'permalink_structure' ) ) {

                if ( isset( $_GET[$wp_rewrite->author_base] ) )
                    $user_LK = intval( $_GET[$wp_rewrite->author_base] );
            }

            if ( '' !== get_site_option( 'permalink_structure' ) || ! $user_LK ) {

                $nicename = false;

                $url    = (isset( $_SERVER['SCRIPT_URL'] )) ? $_SERVER['SCRIPT_URL'] : $_SERVER['REQUEST_URI'];
                $url    = preg_replace( '/\?.*/', '', $url );
                $url_ar = explode( '/', $url );

                foreach ( $url_ar as $key => $u ) {
                    if ( $u != $wp_rewrite->author_base )
                        continue;
                    $nicename = $url_ar[$key + 1];
                    break;
                }

                if ( ! $nicename )
                    return false;

                $user_LK = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM " . $wpdb->prefix . "users WHERE user_nicename='%s'", $nicename ) );
            }
        }

        $user_LK = $user_LK && get_user_by( 'id', $user_LK ) ? $user_LK : 0;

        $usp_office = $user_LK;

        if ( $user_LK && $user_LK != $user_ID ) {
            $usp_userlk_action = usp_get_time_user_action( $user_LK );
        } else if ( $user_LK && $user_LK == $user_ID ) {
            $usp_userlk_action = $usp_current_action;
        }
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
                'label' => __( 'MultiSelect', 'userspace' ),
                'class' => 'USP_Field_MultiSelect'
            ),
            'checkbox'    => array(
                'label' => __( 'Checkbox', 'userspace' ),
                'class' => 'USP_Field_Checkbox'
            ),
            'radio'       => array(
                'label' => __( 'Radiobutton', 'userspace' ),
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

    public function plugin_url() {
        return untrailingslashit( plugins_url( '/', __FILE__ ) );
    }

    public function plugin_path() {
        return untrailingslashit( plugin_dir_path( __FILE__ ) );
    }

    public function template( $name, $file = false ) {
        return new USP_Template( $name, $file );
    }

    public function ajax_url() {
        return admin_url( 'admin-ajax.php', 'relative' );
    }

    public function mailer() {
        /*
         * TODO: Сюда добавить подключение класса отправки сообщений
         */
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

        if ( is_ssl() )
            $upload_dir['baseurl'] = str_replace( 'http://', 'https://', $upload_dir['baseurl'] );

        return apply_filters( 'usp_upload_dir', $upload_dir, $this );
    }

    public function User() {
        return USP_User::instance();
    }

}

function USP() {
    return UserSpace::getInstance();
}

$GLOBALS['userspace'] = USP();
function userspace() {
    global $user_LK;

    do_action( 'usp_area_before' );
    ?>

    <div id="usp-office" <?php usp_office_class(); ?> data-account="<?php echo $user_LK; ?>">

        <?php do_action( 'usp_area_notice' ); ?>

        <?php
        if ( $themePath = USP()->theme->get( 'path' ) ) {
            USP()->template( 'office.php', $themePath )->include();
        } else {
            echo '<h3>' . __( 'Office templates not found!', 'userspace' ) . '</h3>';
        }
        ?>

    </div>

    <?php
    do_action( 'usp_area_after' );
}
