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
class USP_Field_Tel extends USP_Field_Abstract {

	public $required;
	public $placeholder;
	public $maxlength;
	public $pattern;
	public $class;

	function __construct( $args ) {
		parent::__construct( $args );
	}

	function get_options() {

		return array(
			array(
				'slug'		 => 'placeholder',
				'default'	 => $this->placeholder,
				'type'		 => 'text',
				'title'		 => __( 'Placeholder', 'userspace' )
			),
			array(
				'slug'		 => 'maxlength',
				'default'	 => $this->maxlength,
				'type'		 => 'number',
				'title'		 => __( 'Maxlength', 'userspace' ),
				'notice'	 => __( 'maximum number of symbols per field', 'userspace' )
			),
			array(
				'type'	 => 'text',
				'slug'	 => 'pattern',
				'title'	 => __( 'Phone mask', 'userspace' ),
				'notice' => __( 'Example: 8\([0-9]{3}\)[0-9]{3}-[0-9]{2}-[0-9]{2} Result: 8(900)123-45-67', 'userspace' ),
			)
		);
	}

	function get_input() {
		return '<input type="' . $this->type . '" ' . $this->get_pattern() . ' ' . $this->get_maxlength() . ' ' . $this->get_required() . ' ' . $this->get_placeholder() . ' ' . $this->get_class() . ' name="' . $this->input_name . '" id="' . $this->input_id . '" value=\'' . $this->value . '\'/>';
	}

}
