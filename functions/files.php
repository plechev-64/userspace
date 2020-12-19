<?php

function usp_get_wp_upload_dir() {
	if ( defined( 'MULTISITE' ) ) {
		$upload_dir = array(
			'basedir'	 => WP_CONTENT_DIR . '/uploads',
			'baseurl'	 => WP_CONTENT_URL . '/uploads'
		);
	} else {
		$upload_dir = wp_upload_dir();
	}

	if ( is_ssl() )
		$upload_dir['baseurl'] = str_replace( 'http://', 'https://', $upload_dir['baseurl'] );

	return $upload_dir;
}

//получение абсолютного пути до указанного файла шаблона
function usp_get_template_path( $temp_name, $path = false ) {
	return USP()->template($temp_name, $path)->get_path();
}

//подключение указанного файла шаблона с выводом
function usp_include_template( $temp_name, $path = false, $data = false ) {
	return USP()->template($temp_name, $path)->include($data);
}

//подключение указанного файла шаблона без вывода
function usp_get_include_template( $temp_name, $path = false, $data = false ) {
	return USP()->template($temp_name, $path)->get_content($data);
}

//форматирование абсолютного пути в урл
function usp_path_to_url( $path ) {
	$siteUrl = is_ssl() ? str_replace( 'http://', 'https://', get_site_option( 'siteurl' ) ) : get_site_option( 'siteurl' );
	return untrailingslashit( untrailingslashit( $siteUrl ) . '/' . stristr( $path, basename( content_url() ) ) );
}

//получение абсолютного пути из указанного урла
function usp_path_by_url( $url ) {

	if ( function_exists( 'wp_normalize_path' ) )
		$url = wp_normalize_path( $url );

	$string = stristr( $url, basename( content_url() ) );

	$path = untrailingslashit( dirname( WP_CONTENT_DIR ) . '/' . $string );

	return $path;
}

function usp_check_jpeg( $f, $fix = false ) {
# [070203]
# check for jpeg file header and footer - also try to fix it
	if ( false !== (@$fd = fopen( $f, 'r+b' )) ) {
		if ( fread( $fd, 2 ) == chr( 255 ) . chr( 216 ) ) {
			fseek( $fd, -2, SEEK_END );
			if ( fread( $fd, 2 ) == chr( 255 ) . chr( 217 ) ) {
				fclose( $fd );
				return true;
			} else {
				if ( $fix && fwrite( $fd, chr( 255 ) . chr( 217 ) ) ) {
					return true;
				}
				fclose( $fd );
				return false;
			}
		} else {
			fclose( $fd );
			return false;
		}
	} else {
		return false;
	}
}

function usp_get_mime_type_by_ext( $file_ext ) {

	if ( ! $file_ext )
		return false;

	$mimes = get_allowed_mime_types();

	foreach ( $mimes as $type => $mime ) {
		if ( strpos( $type, $file_ext ) !== false ) {
			return $mime;
		}
	}

	return false;
}

function usp_get_mime_types( $ext_array ) {

	if ( ! $ext_array )
		return false;

	$mTypes = array();

	foreach ( $ext_array as $ext ) {
		if ( ! $ext )
			continue;
		$mTypes[] = usp_get_mime_type_by_ext( $ext );
	}

	return $mTypes;
}

/* 22-06-2015 Удаление папки с содержимым */
function usp_remove_dir( $dir ) {
	$dir	 = untrailingslashit( $dir );
	if ( ! is_dir( $dir ) )
		return false;
	if ( $objs	 = glob( $dir . "/*" ) ) {
		foreach ( $objs as $obj ) {
			is_dir( $obj ) ? usp_remove_dir( $obj ) : unlink( $obj );
		}
	}
	rmdir( $dir );
}
