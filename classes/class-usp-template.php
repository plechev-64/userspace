<?php

class USP_Template {

	public $name;
	public $file;

	function __construct( $name, $file = false ) {
		$this->name	 = $name;
		$this->file	 = $file;
	}

	function include( $vars = false ) {

		if ( ! empty( $vars ) && is_array( $vars ) ) {
			extract( $vars );
		}

		$path = $this->get_path();

		if ( ! $path )
			return false;

		do_action( 'usp_include_template_before', $this->name, $path );

		include $path;

		do_action( 'usp_include_template_after', $this->name, $path );
	}

	function get_content( $vars = false ) {

		ob_start();

		$this->include( $vars );

		$content = ob_get_contents();

		ob_end_clean();

		return $content;
	}

	function get_path() {

		$path = ($this->file) ? plugin_dir_path( $this->file ) . 'templates' : USP_PATH . '/templates';

		$path .= '/' . $this->name;

		$path = apply_filters( 'usp_template_path', $path, $this->name );

		if ( ! file_exists( $path ) )
			return false;

		return $path;
	}

}

