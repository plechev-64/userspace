<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class-usp-custom-field-text
 *
 * @author Андрей
 */
class USP_Field_Custom extends USP_Field_Abstract {

	public $content;

	function __construct( $args ) {

		parent::__construct( $args );
	}

	function get_input() {
		return $this->content ? $this->content : false;
	}

}
