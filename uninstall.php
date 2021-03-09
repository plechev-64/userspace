<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

require_once 'classes/class-usp-install.php';
require_once 'functions/plugin-pages.php';
require_once 'functions/files.php';

$upload_dir = usp_get_wp_upload_dir();
define( 'USP_UPLOAD_PATH', $upload_dir['basedir'] . '/usp-uploads/' );
define( 'USP_TAKEPATH', WP_CONTENT_DIR . '/userspace/' );

// Deleting the created roles
USP_Install::remove_roles();

// Deleting cron's schedules
wp_clear_scheduled_hook( 'usp_cron_hourly_schedule' );
wp_clear_scheduled_hook( 'usp_cron_twicedaily_schedule' );
wp_clear_scheduled_hook( 'usp_cron_daily_schedule' );

// Cleaning up on the server
usp_remove_dir( USP_TAKEPATH );
usp_remove_dir( USP_UPLOAD_PATH );

// Deleting tables and plugin settings
$tables = $wpdb->get_results( "SELECT table_name FROM INFORMATION_SCHEMA.TABLES WHERE table_name like '%usp_%'" );
if ( $tables ) {
    foreach ( $tables as $tables ) {
        $wpdb->query( "DROP TABLE IF EXISTS " . $tables->table_name );
    }
}

$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '%usp%'" );
$wpdb->query( "DELETE FROM $wpdb->usermeta WHERE meta_key LIKE '%usp%'" );

// turn on the display of the admin panel for all users of the site
$wpdb->update(
    $wpdb->prefix . 'usermeta', array( 'meta_value' => 'true' ), array( 'meta_key' => 'show_admin_bar_front' )
);

// deleting all the plugin pages
usp_delete_plugin_pages();
