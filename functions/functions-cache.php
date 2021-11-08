<?php

/**
 * Add content to the file cache.
 *
 * @param   $string     string  Unique ID.
 * @param   $content    string  Content that we will add to the file cache.
 * @param   $force      bool    Forced update of this cache.
 *                              Default: false
 *
 * @return  false|mixed         Contents of the cache file
 *
 * @see     USP_Cache
 *
 * @since   1.0.0
 */
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


// clearing the plugin cache once a day
add_action( 'usp_cron_daily', 'usp_clear_cache', 20 );
/**
 *  Clear all cache.
 *
 * @see     USP_Cache
 *
 * @since   1.0.0
 */
function usp_clear_cache() {
	$usp_cache = new USP_Cache();
	$usp_cache->clear_cache();
}

/**
 * Deleting a specific cache file.
 *
 * @param   $string string  Unique ID.
 *
 * @see     USP_Cache
 *
 * @since   1.0.0
 */
function usp_delete_file_cache( $string ) {
	$usp_cache = new USP_Cache();
	$usp_cache->get_file( $string );
	$usp_cache->delete_file();
}

/**
 * Get cache file
 *
 * @param   $string string  Unique ID.
 * @param   $force
 *
 * @return  false|string
 *
 * @see     USP_Cache
 *
 * @since   1.0.0
 */
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
