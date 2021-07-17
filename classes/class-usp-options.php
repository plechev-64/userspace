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
    private $options            = [];

    public static function getInstance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    private function __construct() {

        if ( isset( self::$_instance ) ) {
            return;
        }

        $this->options = get_site_option( 'usp_global_options' );
    }

    function get_options() {
        return $this->options;
    }

    function update_options( $options = false ) {
        return update_site_option( 'usp_global_options', $options ?: $this->options );
    }

    function set_options( $options ) {

        if ( ! is_array( $options ) ) {
            return false;
        }

        foreach ( $options as $name => $value ) {
            $this->set( $name, $value );
        }
    }

    function reset_options( $options ) {

        if ( ! is_array( $options ) ) {
            return false;
        }

        foreach ( $options as $name ) {
            $this->clear( $name );
        }
    }

    function is_set( $name ) {
        return isset( $this->options[ $name ] );
    }

    function get( $name, $default = false ) {

        if ( $this->is_set( $name ) ) {
            if ( $this->options[ $name ] || is_numeric( $this->options[ $name ] ) ) {
                return $this->options[ $name ];
            }
        }

        return $default;
    }

    function set( $name, $value = null ) {

        if ( is_null( $value ) ) {
            unset( $this->options[ $name ] );
        } else {
            $this->options[ $name ] = $value;
        }

        return true;
    }

    function set_is_null( $name, $value ) {
        if ( ! $this->is_set( $name ) ) {
            $this->set( $name, $value );
        }
    }

    function clear( $name ) {
        if ( ! $this->is_set( $name ) ) {
            return false;
        }

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
