<?php

class USP_Field_Number extends USP_Field_Abstract {

	public $required;
	public $placeholder;
	public $value_max;
	public $value_min;
	public $value_step;
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
			array(
				'slug'    => 'value_step',
				'default' => $this->value_step,
				'type'    => 'select',
				'title'   => __( 'Step', 'userspace' ),
				'values'  => array( '1' => 1, '0.1' => 0.1, '0.01' => 0.01, '0.001' => 0.001, '0.0001' => 0.0001 )
			),
		);
	}

	function get_input() {
		return '<input type="' . $this->type . '" ' . $this->get_min() . ' ' . $this->get_max() . ' ' . $this->get_required() . ' ' . $this->get_placeholder() . ' ' . $this->get_class() . ' name="' . $this->input_name . '" id="' . $this->input_id . '" value=\'' . $this->value . '\' step=\'' . $this->value_step . '\'/>';
	}

	function get_filter_value() {
		return '<a href="' . $this->get_filter_url() . '" target="_blank">' . $this->value . '</a>';
	}

	function is_valid_value( $value ) {

		if ( ! is_numeric( $value ) ) {
			return false;
		}

		if ( ! empty( $this->value_max ) && ( $value > $this->value_max ) ) {
			return false;
		}

		if ( ! empty( $this->value_min ) && ( $value < $this->value_min ) ) {
			return false;
		}

		$max_precision = strlen( $this->value_step ) - strrpos( $this->value_step, '.' ) - 1;

		[ , $value_fraction ] = explode( '.', $value );

		if ( ! empty( $value_fraction ) && strlen( $value_fraction ) > $max_precision ) {
			return false;
		}

		return true;
	}

}
