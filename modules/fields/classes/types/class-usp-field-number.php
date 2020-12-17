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
class USP_Field_Number extends USP_Field_Abstract {

	public $required;
	public $placeholder;
	public $value_max;
	public $value_min;
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
				'title'		 => __( 'Placeholder', 'usp' )
			),
			array(
				'slug'		 => 'value_min',
				'default'	 => $this->value_min,
				'type'		 => 'number',
				'title'		 => __( 'Min', 'usp' ),
			),
			array(
				'slug'		 => 'value_max',
				'default'	 => $this->value_max,
				'type'		 => 'number',
				'title'		 => __( 'Max', 'usp' ),
			),
		);
	}

	function get_input() {
		return '<input type="' . $this->type . '" ' . $this->get_min() . ' ' . $this->get_max() . ' ' . $this->get_required() . ' ' . $this->get_placeholder() . ' ' . $this->get_class() . ' name="' . $this->input_name . '" id="' . $this->input_id . '" value=\'' . $this->value . '\'/>';
	}

	function get_filter_value() {
		return '<a href="' . $this->get_filter_url() . '" target="_blank">' . $this->value . '</a>';
	}

}
