<?php

class USP_Field_Radio extends USP_Field_Abstract {

	public $required;
	public $values;
	public $display = 'inline';
	public $empty_first;
	public $empty_value;
	public $childrens;

	function __construct( $args ) {

		if ( isset( $args['empty-first'] ) ) {
			$args['empty_first'] = $args['empty-first'];
		}

		if ( isset( $args['empty-value'] ) ) {
			$args['empty_value'] = $args['empty-value'];
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

		if ( ! $this->values ) {
			return false;
		}

		$input_id = $this->input_id . '_' . $this->rand;

		$content = '';

		if ( $this->empty_first ) {
			$content .= '<span class="usp-radio-box checkbox-display-' . esc_attr( $this->display ) . ' usps__inline usps__relative">';
			$content .= '<input type="radio" ' . $this->get_required() . ' ' . checked( $this->value, '', false ) . ' id="' . esc_attr( $input_id ) . '" data-slug="' . esc_attr( $this->slug ) . '" name="' . esc_attr( $this->input_name ) . '" value="' . esc_attr( $this->empty_value ) . '"> ';
			$content .= '<label class="usp-label usps usps__ai-center usps__no-select" for="' . esc_attr( $input_id ) . '">' . esc_html( $this->empty_first ) . '</label>';
			$content .= '</span>';
		}

		$a = 0;

		if ( ! $this->empty_first && ! $this->value ) {
			$this->value = ( $this->value_in_key ) ? $this->values[0] : 0;
		}

		foreach ( $this->values as $k => $value ) {

			if ( $this->value_in_key ) {
				$k = $value;
			}

			$k        = trim( $k );
			$input_id = $this->input_id . '_' . $a . '_' . $this->rand;

			$content .= '<span class="usp-radio-box checkbox-display-' . esc_attr( $this->display ) . ' usps__inline usps__relative" data-value="' . esc_attr( $k ) . '">';
			$content .= '<input type="radio" ' . $this->get_required() . ' ' . checked( $this->value, $k, false ) . ' ' . $this->get_class() . ' id="' . esc_attr( $input_id ) . '" data-slug="' . esc_attr( $this->slug ) . '" name="' . esc_attr( $this->input_name ) . '" value="' . esc_attr( $k ) . '"> ';
			$content .= '<label class="usp-label usps usps__ai-center usps__no-select" for="' . esc_attr( $input_id ) . '">' . esc_html( $value ) . '</label>';
			$content .= '</span>';

			$a ++;
		}

		return $content;
	}

	function get_filter_value() {
		return '<a href="' . esc_url( $this->get_filter_url() ) . '" target="_blank">' . esc_html( $this->value ) . '</a>';
	}

	function is_valid_value( $value ) {

		if ( is_array( $value ) && count( $value ) > 1 ) {
			return false;
		}

		$valid_values = $this->value_in_key ? $this->values : array_keys( $this->values );

		return (bool) array_intersect( (array) $value, $valid_values );
	}

}
