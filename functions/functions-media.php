<?php

use USP\Core\Query\DefaultTable\TempMediaQuery;

function usp_get_image_gallery( $args ) {

	$gallery = new USP_Image_Gallery( $args );

	return $gallery->get_gallery();
}

function usp_add_temp_media( $args ) {
	global $user_ID;

	$session_id = ! empty( $_COOKIE['PHPSESSID'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['PHPSESSID'] ) ) : 'none';

	$args = wp_parse_args( $args, [
		'media_id'    => '',
		'user_id'     => $user_ID,
		'uploader_id' => '',
		'session_id'  => $user_ID ? '' : $session_id,
		'upload_date' => current_time( 'mysql' )
	] );

	if ( ! $args['media_id'] ) {
		return false;
	}

	global $wpdb;

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
	if ( ! $wpdb->insert( USP_PREF . 'temp_media', $args ) ) {
		return false;
	}

	do_action( 'usp_add_temp_media', $args['media_id'] );

	return $args['media_id'];
}

function usp_update_temp_media( $update, $where ) {
	global $wpdb;

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
	return $wpdb->update( USP_PREF . 'temp_media', $update, $where );
}

function usp_delete_temp_media( $media_id ) {
	global $wpdb;

	// phpcs:ignore
	return $wpdb->query( $wpdb->prepare( "DELETE FROM " . USP_PREF . "temp_media WHERE media_id = %d", $media_id ) );
}

function usp_delete_temp_media_by_args( $args ) {
	$medias = usp_get_temp_media( $args );

	if ( ! $medias ) {
		return false;
	}

	foreach ( $medias as $media ) {
		usp_delete_temp_media( $media->media_id );
	}
}

function usp_get_temp_media( $args = false ) {
	return ( new TempMediaQuery() )->parse( $args )->get_results();
}

add_action( 'delete_attachment', 'usp_delete_attachment_temp_gallery', 10 );
function usp_delete_attachment_temp_gallery( $attachment_id ) {
	usp_delete_temp_media( $attachment_id );
}

add_action( 'usp_cron_twicedaily', 'usp_delete_daily_old_temp_attachments', 10 );
function usp_delete_daily_old_temp_attachments() {
	$medias = usp_get_temp_media( [
		'date_query' => [
			[
				'last' => '1 DAY'
			]
		]
	] );

	if ( ! $medias ) {
		return;
	}

	foreach ( $medias as $media ) {
		wp_delete_attachment( $media->media_id, true );
	}
}

// crop images
function usp_crop( $filesource, $width, $height, $file ) {
	$image = wp_get_image_editor( $filesource );

	if ( ! is_wp_error( $image ) ) {
		$image->resize( $width, $height, true );
		$image->save( $file );
	}

	return $image;
}
