<?php

class USP_Field_Switch extends USP_Field_Abstract {

	public $values;
	public $text;

	function __construct( $args ) {
		parent::__construct( $args );
	}

	function get_options() {

		return [
			[
				'slug'    => 'switch',
				'default' => $this->values,
			]
		];
	}

	function get_value() {

		if ( is_null( $this->value ) ) {
			return false;
		}

		return $this->value ? $this->text['on'] : $this->text['off'];
	}

	function get_input() {

		if ( ! $this->slug ) {
			return false;
		}

		$input_id = $this->input_id . '-' . $this->rand;

		$data_off = ( $this->text['off'] ) ? 'data-off="' . esc_attr( $this->text['off'] ) . '"' : '';
		$data_on  = ( $this->text['on'] ) ? 'data-on="' . esc_attr( $this->text['on'] ) . '"' : '';

		$content = '<label class="usp-switch-box usps__relative" for="' . esc_attr( $input_id ) . '">';
		$content .= '<input type="hidden" class="switch-field-hidden" id="' . esc_attr( $this->input_id ) . '" value="' . esc_attr( $this->value ) . '" name="' . esc_attr( $this->input_name ) . '">';
		$content .= '<input type="checkbox" ' . checked( $this->value, 1, false ) . ' id="' . esc_attr( $input_id ) . '" ' . $this->get_class() . ' value="1" onclick="this.previousSibling.value=1-this.previousSibling.value"/> ';
		$content .= '<span class="usp-switch-label usps__relative" ' . $data_off . ' ' . $data_on . '></span>';
		$content .= '<span class="usp-switch-handle"></span>';
		$content .= '</label>';

		return $content;
	}

	function is_valid_value( $value ) {
		return $value === 1 || $value === 0;
	}

}
