<?php

function usp_get_wp_upload_dir() {
	if ( defined( 'MULTISITE' ) ) {
		$upload_dir = [
			'basedir' => WP_CONTENT_DIR . '/uploads',
			'baseurl' => WP_CONTENT_URL . '/uploads'
		];
	} else {
		$upload_dir = wp_upload_dir();
	}

	if ( is_ssl() ) {
		/** @noinspection HttpUrlsUsage */
		$upload_dir['baseurl'] = str_replace( 'http://', 'https://', $upload_dir['baseurl'] );
	}

	return $upload_dir;
}

/**
 * Getting the absolute path to the specified template file.
 *
 * @param   $temp_name
 * @param   $path       string  Default: false
 *
 * @return  false|mixed|void
 *
 * @since   1.0.0
 */
function usp_get_template_path( $temp_name, $path = false ) {
	return USP()->template( $temp_name, $path )->get_path();
}

/**
 * Connecting a template file with output.
 *
 * @param   $temp_name  string  Template file name.
 * @param   $path       string  Path to file.
 *                              Default: false
 * @param   $data       array   Array of additional data.
 *
 * @return  void
 *
 * @since   1.0.0
 */
function usp_include_template( $temp_name, $path = false, $data = false ) {
	USP()->template( $temp_name, $path )->include( $data );
}

/**
 * Connecting the specified template file without output.
 *
 * Connects templates file from:
 * the current WordPress theme (/wp-content/themes/your-active-WP-theme/userspace/templates/$temp_name)
 * or from a special plugin directory (/wp-content/userspace/templates/$temp_name)
 * if not exist, it will connect the file from the plugin folder (/wp-content/plugins/your-plugin/templates/$temp_name)
 *
 * @param   $temp_name  string  Template file name.
 * @param   $path       string  Path to file.
 *                              Default: false
 * @param   $data       array   Array of additional data.
 *
 * @return  string      HTML content of a specific template.
 *
 * @see     https://docs.user-space.com/document/template-structure/
 *
 * @since   1.0.0
 *
 */
function usp_get_include_template( $temp_name, $path = false, $data = false ): string {
	return USP()->template( $temp_name, $path )->get_content( $data );
}


/**
 * Formatting an absolute path in url.
 *
 * @param   $path
 *
 * @return  string
 *
 * @since   1.0.0
 */
function usp_path_to_url( $path ): string {
	/** @noinspection HttpUrlsUsage */
	$siteUrl = is_ssl() ? str_replace( 'http://', 'https://', get_site_option( 'siteurl' ) ) : get_site_option( 'siteurl' );

	return untrailingslashit( untrailingslashit( $siteUrl ) . '/' . stristr( $path, basename( content_url() ) ) );
}

/**
 * Getting the absolute path from the specified url.
 *
 * @param   $url
 *
 * @return  string
 *
 * @since   1.0.0
 */
function usp_path_by_url( $url ): string {
	if ( function_exists( 'wp_normalize_path' ) ) {
		$url = wp_normalize_path( $url );
	}

	$string = stristr( $url, basename( content_url() ) );

	return untrailingslashit( dirname( WP_CONTENT_DIR ) . '/' . $string );
}

function usp_get_mime_type_by_ext( $file_ext ): ?string {
	if ( ! $file_ext ) {
		return null;
	}

	$mimes = get_allowed_mime_types();

	foreach ( $mimes as $type => $mime ) {
		if ( strpos( $type, $file_ext ) !== false ) {
			return $mime;
		}
	}

	return null;
}

function usp_get_mime_types( $ext_array ): array {
	if ( ! $ext_array ) {
		return [];
	}

	$mTypes = [];

	foreach ( $ext_array as $ext ) {
		if ( ! $ext ) {
			continue;
		}
		$mTypes[] = usp_get_mime_type_by_ext( $ext );
	}

	return $mTypes;
}

/**
 * Deleting a folder with content.
 *
 * @param   $path
 *
 * @return  false|void
 *
 * @since   1.0.0
 */
function usp_remove_dir( $path ) {
	$dir = untrailingslashit( $path );
	if ( ! is_dir( $dir ) ) {
		return false;
	}

	$objs = glob( "$dir/{,.}[!.,!..]*", GLOB_BRACE );

	if ( $objs ) {
		foreach ( $objs as $obj ) {
			is_dir( $obj ) ? usp_remove_dir( $obj ) : unlink( $obj );
		}
	}

	rmdir( $dir );
}
