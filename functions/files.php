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
		$upload_dir['baseurl'] = str_replace( 'http://', 'https://', $upload_dir['baseurl'] );
	}

	return $upload_dir;
}

// getting the absolute path to the specified template file
function usp_get_template_path( $temp_name, $path = false ) {
	return USP()->template( $temp_name, $path )->get_path();
}

// connecting a template file with output
function usp_include_template( $temp_name, $path = false, $data = false ) {
	return USP()->template( $temp_name, $path )->include( $data );
}

/**
 * Connecting the specified template file without output
 *
 * Connects templates file from:
 * the current WordPress theme (/wp-content/themes/your-active-WP-theme/userspace/templates/$temp_name)
 * or from a special plugin directory (/wp-content/userspace/templates/$temp_name)
 * if not exist, it will connect the file from the plugin folder (/wp-content/plugins/your-plugin/templates/$temp_name)
 *
 * @param string $temp_name Template file name.
 * @param string $path Path to file.
 * @param array $data Array of additional data.
 *
 * @return string       HTML content of a specific template.
 * @since 1.0.0
 *
 */
function usp_get_include_template( $temp_name, $path = false, $data = false ) {
	return USP()->template( $temp_name, $path )->get_content( $data );
}

// formatting an absolute path in url
function usp_path_to_url( $path ) {
	$siteUrl = is_ssl() ? str_replace( 'http://', 'https://', get_site_option( 'siteurl' ) ) : get_site_option( 'siteurl' );

	return untrailingslashit( untrailingslashit( $siteUrl ) . '/' . stristr( $path, basename( content_url() ) ) );
}

// getting the absolute path from the specified url
function usp_path_by_url( $url ) {

	if ( function_exists( 'wp_normalize_path' ) ) {
		$url = wp_normalize_path( $url );
	}

	$string = stristr( $url, basename( content_url() ) );

	$path = untrailingslashit( dirname( WP_CONTENT_DIR ) . '/' . $string );

	return $path;
}

function usp_get_mime_type_by_ext( $file_ext ) {

	if ( ! $file_ext ) {
		return false;
	}

	$mimes = get_allowed_mime_types();

	foreach ( $mimes as $type => $mime ) {
		if ( strpos( $type, $file_ext ) !== false ) {
			return $mime;
		}
	}

	return false;
}

function usp_get_mime_types( $ext_array ) {

	if ( ! $ext_array ) {
		return false;
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

// Deleting a folder with content
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
