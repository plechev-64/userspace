<?php

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
				'slug'    => 'placeholder',
				'default' => $this->placeholder,
				'type'    => 'text',
				'title'   => __( 'Placeholder', 'userspace' )
			),
			array(
				'slug'    => 'value_min',
				'default' => $this->value_min,
				'type'    => 'number',
				'title'   => __( 'Min', 'userspace' ),
			),
			array(
				'slug'    => 'value_max',
				'default' => $this->value_max,
				'type'    => 'number',
				'title'   => __( 'Max', 'userspace' ),
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
