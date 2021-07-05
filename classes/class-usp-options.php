<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class-usp-global-options
 *
 * @author Андрей
 */
class USP_Options {

	protected static $_instance = null;

	public static function getInstance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	function get_options() {
		return USP()->options;
	}

	function update_options( $options = false ) {
		return update_site_option( 'usp_global_options', $options ?: USP()->options );
	}

	function set_options( $options ) {

		if ( ! is_array( $options ) )
			return false;

		foreach ( $options as $name => $value ) {
			$this->set( $name, $value );
		}
	}

	function reset_options( $options ) {

		if ( ! is_array( $options ) )
			return false;

		foreach ( $options as $name ) {
			$this->clear( $name );
		}
	}

	function is_set( $name ) {
		return isset( USP()->options[$name] );
	}

	function get( $name, $default = false ) {

		if ( $this->is_set( $name ) ) {
			if ( USP()->options[$name] || is_numeric( USP()->options[$name] ) ) {
				return USP()->options[$name];
			}
		}

		return $default;
	}

	function set( $name, $value = null ) {

		if ( is_null( $value ) ) {
			unset( USP()->options[$name] );
		} else {
			USP()->options[$name] = $value;
		}

		return true;
	}

	function set_is_null( $name, $value ) {
		if ( ! $this->is_set( $name ) )
			$this->set( $name, $value );
	}

	function clear( $name ) {
		if ( ! $this->is_set( $name ) )
			return false;

		return $this->set( $name );
	}

	function update( $name, $value ) {
		if ( $this->set( $name, $value ) ) {
			$this->update_options();
		}
	}

	function delete( $name ) {
		if ( $this->clear( $name ) ) {
			$this->update_options();
		}
	}

}
