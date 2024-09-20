<?php

class Option extends Field {
	static function setup_option( array $args ): ?FieldAbstract {

		if ( ! isset( $args['slug'] ) ) {
			if ( $args['type'] == 'custom' ) {
				$args['slug'] = md5( current_time( 'mysql' ) );
			} else {
				return null;
			}
		}

		$object = parent::setup( $args );

		$object->extend = ( isset( $args['extend'] ) ) ? $args['extend'] : false;
		$object->local  = ( isset( $args['local'] ) ) ? $args['local'] : false;

		return $object;
	}

}
