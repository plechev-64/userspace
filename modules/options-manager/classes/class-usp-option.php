<?php

class USP_Option extends USP_Field {
	static function setup_option( $args ) {

		if ( ! isset( $args['slug'] ) ) {
			if ( $args['type'] == 'custom' ) {
				$args['slug'] = md5( current_time( 'mysql' ) );
			} else {
				return false;
			}
		}

		$object = parent::setup( $args );

		$object->extend = ( isset( $args['extend'] ) ) ? $args['extend'] : false;
		$object->local  = ( isset( $args['local'] ) ) ? $args['local'] : false;

		return $object;
	}

}
