<?php

class USP_Field_Select extends USP_Field_Abstract {

	public $required;
	public $empty_first;
	public $values;
	public $childrens;
	public $key_in_data;

	function __construct( $args ) {

		if ( isset( $args['empty-first'] ) ) {
			$args['empty_first'] = $args['empty-first'];
		}

		parent::__construct( $args );
	}

	function get_options() {

		return [
			[
				'slug'    => 'empty_first',
				'default' => $this->empty_first,
				'type'    => 'text',
				'title'   => __( 'First value', 'userspace' ),
				'notice'  => __( 'Name of the first blank value, for example: "Not selected"', 'userspace' )
			],
			[
				'slug'    => 'values',
				'default' => $this->values,
				'type'    => 'dynamic',
				'title'   => __( 'Specify options', 'userspace' ),
				'notice'  => __( 'Specify each option in a separate field', 'userspace' )
			]
		];
	}

	function get_value() {

		if ( is_null( $this->value ) ) {
			return false;
		}

		if ( $this->value_in_key ) {
			return $this->value;
		}

		return $this->values[ $this->value ];
	}

	function get_input() {

		$content = '<select ' . $this->get_required() . ' name="' . esc_attr( $this->input_name ) . '" id="' . esc_Attr( $this->input_id ) . '" ' . $this->get_class() . '>';

		if ( $this->empty_first ) {
			$content .= '<option value="">' . esc_html( $this->empty_first ) . '</option>';
		}

		if ( $this->values ) {
			foreach ( $this->values as $k => $value ) {

				$data = ( $this->key_in_data ) ? 'data-key="' . esc_attr( $k ) . '"' : '';

				if ( $this->value_in_key ) {
					$k = $value;
				}

				$content .= '<option ' . selected( $this->value, $k, false ) . ' ' . $data . ' value="' . esc_attr( $k ) . '">' . esc_html( $value ) . '</option>';
			}
		}

		$content .= '</select>';

		return $content;
	}

	function get_filter_value() {
		return '<a href="' . esc_url( $this->get_filter_url() ) . '" target="_blank">' . esc_html( $this->value ) . '</a>';
	}

	function is_valid_value( $value ) {

		if ( is_array( $value ) ) {
			return false;
		}

		$valid_values = $this->value_in_key ? $this->values : array_keys( $this->values );

		return in_array( $value, $valid_values );
	}

}
