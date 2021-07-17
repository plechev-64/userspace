<?php

// clearing the plugin cache once a day
add_action( 'usp_cron_daily', 'usp_clear_cache', 20 );
function usp_clear_cache() {
	$usp_cache = new USP_Cache();
	$usp_cache->clear_cache();
}

// deleting a specific cache file
function usp_delete_file_cache( $string ) {
	$usp_cache = new USP_Cache();
	$usp_cache->get_file( $string );
	$usp_cache->delete_file();
}

function usp_cache_get( $string, $force = false ) {

	$cache = new USP_Cache();

	if ( $cache->is_cache || $force ) {

		$file = $cache->get_file( $string );

		if ( ! $file->need_update ) {

			return $cache->get_cache();
		}
	}

	return false;
}

function usp_cache_add( $string, $content, $force = false ) {

	$cache = new USP_Cache();

	if ( $cache->is_cache || $force ) {

		$file = $cache->get_file( $string );

		if ( $file->need_update ) {

			return $cache->update_cache( $content );
		}
	}

	return false;
}
