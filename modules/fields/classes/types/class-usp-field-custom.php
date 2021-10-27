<?php

class USP_Field_Custom extends USP_Field_Abstract {

	public $content;

	function __construct( $args ) {

		parent::__construct( $args );
	}

	function get_input() {
		return $this->content ?: false;
	}

}
