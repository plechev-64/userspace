<?php

class Log {

	private string $log_path;

	public function __construct( $args = false ) {

		if ( $args ) {
			$this->init_properties( $args );
		}

		if ( ! $this->log_path ) {

			$logDir = USP_TAKEPATH . 'logs/';

			if ( ! file_exists( $logDir ) ) {
				wp_mkdir_p( $logDir );
			}

			$this->log_path = $logDir . date( 'Y-m-d' ) . '.log';
		}
	}

	private function init_properties( array $args ) {
		$properties = get_class_vars( get_class( $this ) );

		foreach ( $properties as $name => $val ) {
			if ( isset( $args[ $name ] ) ) {
				$this->$name = $args[ $name ];
			}
		}
	}

	public function insert_title( string $title ) {

		$this->insert_log( date( 'H:i:s' ) . " " . $title );
	}

	public function insert_log( string|array $data ) {

		if ( ! is_string( $data ) ) {
			$data = print_r( $data, true );
		}

		file_put_contents( $this->log_path, $data . "\n", FILE_APPEND );
	}

}
