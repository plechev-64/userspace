<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

require_once 'functions/plugin-pages.php';
require_once 'functions/files.php';

$upload_dir = usp_get_wp_upload_dir();
define( 'USP_UPLOAD_PATH', $upload_dir['basedir'] . '/usp-uploads/' );
define( 'USP_TAKEPATH', WP_CONTENT_DIR . '/userspace/' );

// Deleting the created roles
if ( class_exists( 'WP_Roles' ) ) {
	remove_role( 'need-confirm' );
	remove_role( 'banned' );
}

// Deleting cron's schedules
wp_clear_scheduled_hook( 'usp_cron_hourly_schedule' );
wp_clear_scheduled_hook( 'usp_cron_twicedaily_schedule' );
wp_clear_scheduled_hook( 'usp_cron_daily_schedule' );

// Cleaning up on the server
usp_remove_dir( USP_TAKEPATH );
usp_remove_dir( USP_UPLOAD_PATH );

global $wpdb;

// Deleting tables
$tables = [
	$wpdb->base_prefix . 'usp_temp_media',
	$wpdb->base_prefix . 'usp_users_actions'
];
$wpdb->query( "DROP TABLE IF EXISTS `" . implode( '`, `', $tables ) . "`" );


// Deleting options
$all_options = [
	'usp_global_options',
	'usp_plugin_pages',
	'usp_version',
	'widget_usp-new-users',
	'widget_usp-online-users',
	'widget_usp-primary-panel'
];

foreach ( $all_options as $option ) {
	delete_option( $option );
}

// Deleting meta
$wpdb->query( "DELETE FROM $wpdb->usermeta WHERE meta_key LIKE 'usp_\%'" );

// turn on the display of the admin panel for all users of the site
$wpdb->update(
	$wpdb->usermeta,
	[ 'meta_value' => 'true' ],
	[ 'meta_key' => 'show_admin_bar_front' ]
);

// deleting all the plugin pages
usp_delete_plugin_pages();
