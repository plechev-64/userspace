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
class USP_Field_TextArea extends USP_Field_Abstract {

	public $required;
	public $placeholder;
	public $maxlength;

	function __construct( $args ) {
		parent::__construct( $args );
	}

	function get_options() {

		return array(
			array(
				'slug'		 => 'placeholder',
				'default'	 => $this->placeholder,
				'type'		 => 'text',
				'title'		 => __( 'Placeholder', 'usp' )
			),
			array(
				'slug'		 => 'maxlength',
				'default'	 => $this->maxlength,
				'type'		 => 'number',
				'title'		 => __( 'Maxlength', 'usp' ),
				'notice'	 => __( 'maximum number of symbols per field', 'usp' )
			)
		);
	}

	function get_input() {
		return '<textarea name="' . $this->input_name . '" ' . $this->get_maxlength() . ' ' . $this->get_required() . ' ' . $this->get_placeholder() . ' ' . $this->get_class() . ' id="' . $this->input_id . '" rows="5" cols="50">' . $this->value . '</textarea>';
	}

	function get_value() {

		if ( ! $this->value )
			return false;

		return nl2br( $this->value );
	}

}
