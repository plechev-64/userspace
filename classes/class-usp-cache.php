<?php

class USP_Cache {

	public $inc_cache;
	public $only_guest;
	public $time_cache;
	public $is_cache;
	public $filepath;
	public $last_update;
	public $file_exists;

	function __construct( $cache_time = 0, $only_guest = false ) {
		global $user_ID;

		$this->inc_cache  = usp_get_option( 'usp_use_cache' );
		$this->only_guest = $only_guest;

		if ( ! $this->only_guest ) {
			$this->only_guest = usp_get_option( 'usp_cache_output' );
		}

		$this->is_cache   = $this->inc_cache && ( ! $this->only_guest || $this->only_guest && ! $user_ID ) ? 1 : 0;
		$this->time_cache = usp_get_option( 'usp_cache_time', 3600 );

		if ( $cache_time ) {
			$this->time_cache = $cache_time;
		}
	}

	function get_file( $string ) {
		$name_cache        = md5( $string );
		$cache_path        = USP_UPLOAD_PATH . 'cache/';
		$filename          = $name_cache . '.txt';
		$this->filepath    = $cache_path . $filename;
		$this->file_exists = 0;

		if ( ! file_exists( $cache_path ) ) {
			mkdir( $cache_path );
			chmod( $cache_path, 0755 );
		}

		$file = [
			'filename' => $filename,
			'filepath' => $this->filepath
		];

		if ( ! file_exists( $this->filepath ) ) {
			$file['need_update'] = 1;
			$file['file_exists'] = 0;

			return ( object ) $file;
		}

		$this->last_update = filemtime( $this->filepath );
		$end_cache         = $this->last_update + $this->time_cache;

		$this->file_exists = 1;

		$file['file_exists'] = 1;
		$file['last_update'] = $this->last_update;
		$file['need_update'] = ( $end_cache < time() ) ? 1 : 0;

		return ( object ) $file;
	}

	function get_cache() {
		if ( ! $this->file_exists ) {
			return false;
		}

		return file_get_contents( $this->filepath ) . '<!-- USP-cache start:' . gmdate( 'd.m.Y H:i', $this->last_update ) . ' time:' . $this->time_cache . ' -->';
	}

	function update_cache( $content ) {
		if ( ! $this->filepath ) {
			return false;
		}
		$f = fopen( $this->filepath, 'w+' );
		fwrite( $f, $content );
		fclose( $f );

		return $content;
	}

	function delete_file() {
		if ( ! $this->file_exists ) {
			return false;
		}
		unlink( $this->filepath );
	}

	function clear_cache() {
		usp_remove_dir( USP_UPLOAD_PATH . 'cache/' );
	}

}
