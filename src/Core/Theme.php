<?php

namespace USP\Core;

class Theme {

	public string $id;
	public string $path;

	public function __construct( array $args ) {
		$this->init_properties( $args );
	}

	private function init_properties( array $args ): void {

		$properties = get_class_vars( get_class( $this ) );

		foreach ( $properties as $name => $val ) {
			if ( isset( $args[ $name ] ) ) {
				$this->$name = $args[ $name ];
			}
		}
	}

	public function get( string $propName ): string {
		return $this->$propName;
	}

	public function is_current( string $path ): bool {
		return $this->get( 'id' ) == plugin_basename( $path );
	}

}
