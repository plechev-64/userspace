<?php

class USP_Theme {

	public $id;
	public $path;

	function __construct( $args ) {
		$this->init_properties( $args );
	}

	function init_properties( $args ) {

		$properties = get_class_vars( get_class( $this ) );

		foreach ( $properties as $name => $val ) {
			if ( isset( $args[$name] ) )
				$this->$name = $args[$name];
		}
	}

	function get( $propName ) {
		return $this->$propName;
	}

	function is_current( $path ) {
		return $this->get( 'id' ) == plugin_basename( $path );
	}

}
