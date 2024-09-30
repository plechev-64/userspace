<?php

namespace USP\Core;

class Options {

	protected static ?Options $_instance	 = null;
	private array $options			 = [];

	public static function getInstance(): ?Options {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	private function __construct() {

		if ( isset( self::$_instance ) ) {
			return;
		}

		$this->options = get_site_option( 'usp_global_options', [] );
	}

	public function get_options(): array {
		return $this->options;
	}

	public function update_options( $options = false ): bool {
		return update_site_option( 'usp_global_options', $options ?: $this->options );
	}

	public function set_options( array $options ): void {
		foreach ( $options as $name => $value ) {
			$this->set( $name, $value );
		}
	}

	public function reset_options( array $options ): void {
		foreach ( $options as $name ) {
			$this->clear( $name );
		}
	}

	public function is_set( $name ): bool {
		return isset( $this->options[ $name ] );
	}

	public function get( string $name, mixed $default = false ): mixed {

		if ( $this->is_set( $name ) ) {
			if ( $this->options[ $name ] || is_numeric( $this->options[ $name ] ) ) {
				return $this->options[ $name ];
			}
		}

		return $default;
	}

	public function set( $name, $value = null ): bool {

		if ( is_null( $value ) ) {
			unset( $this->options[ $name ] );
		} else {
			$this->options[ $name ] = $value;
		}

		return true;
	}

	public function set_is_null( $name, $value ): void {
		if ( ! $this->is_set( $name ) ) {
			$this->set( $name, $value );
		}
	}

	public function clear( $name ): bool {
		if ( ! $this->is_set( $name ) ) {
			return false;
		}

		return $this->set( $name );
	}

	public function update( $name, $value ): void {
		if ( $this->set( $name, $value ) ) {
			$this->update_options();
		}
	}

	public function delete( $name ): void {
		if ( $this->clear( $name ) ) {
			$this->update_options();
		}
	}

}
