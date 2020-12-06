<?php

add_action( 'wp', 'usp_cron_activated' );
function usp_cron_activated() {
	if ( ! wp_next_scheduled( 'usp_cron_hourly_schedule' ) ) {
		wp_schedule_event( time(), 'hourly', 'usp_cron_hourly_schedule' );
	}
	if ( ! wp_next_scheduled( 'usp_cron_twicedaily_schedule' ) ) {
		wp_schedule_event( time() + 900, 'twicedaily', 'usp_cron_twicedaily_schedule' );
	}
	if ( ! wp_next_scheduled( 'usp_cron_daily_schedule' ) ) {
		wp_schedule_event( time() + 1800, 'daily', 'usp_cron_daily_schedule' );
	}
}

add_action( 'usp_cron_hourly_schedule', 'usp_cron_hourly' );
function usp_cron_hourly() {

	usp_add_log(
		__( 'Launch cron events', 'usp' ) . ' usp_cron_hourly'
	);

	do_action( 'usp_cron_hourly' );
}

add_action( 'usp_cron_twicedaily_schedule', 'usp_cron_twicedaily' );
function usp_cron_twicedaily() {

	usp_add_log(
		__( 'Launch cron events', 'usp' ) . ' usp_cron_twicedaily'
	);

	do_action( 'usp_cron_twicedaily' );
}

add_action( 'usp_cron_daily_schedule', 'usp_cron_daily' );
function usp_cron_daily() {

	usp_add_log(
		__( 'Launch cron events', 'usp' ) . ' usp_cron_daily'
	);

	do_action( 'usp_cron_daily' );
}
