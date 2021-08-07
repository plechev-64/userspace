<?php

class USP_Field_Switch extends USP_Field_Abstract {

	public $values;
	public $text;

	function __construct( $args ) {
		parent::__construct( $args );
	}

	function get_options() {

		return array(
			array(
				'slug'    => 'switch',
				'default' => $this->values,
			)
		);
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

		$data_off = ( $this->text['off'] ) ? 'data-off="' . $this->text['off'] . '"' : '';
		$data_on  = ( $this->text['on'] ) ? 'data-on="' . $this->text['on'] . '"' : '';

		$content = '<label class="usp-switch-box usps__relative" for="' . $this->input_id . '-' . $this->rand . '">';
		$content .= '<input type="hidden" class="switch-field-hidden" id="' . $this->input_id . '" value="' . $this->value . '" name="' . $this->input_name . '">';
		$content .= '<input type="checkbox" ' . checked( $this->value, 1, false ) . ' id="' . $this->input_id . '-' . $this->rand . '" ' . $this->get_class() . ' value="1" onclick="this.previousSibling.value=1-this.previousSibling.value"/> ';
		$content .= '<span class="usp-switch-label usps__relative" ' . $data_off . ' ' . $data_on . '></span>';
		$content .= '<span class="usp-switch-handle"></span>';
		$content .= '</label>';

		return $content;
	}

	function is_valid_value( $value ) {
		return $value === 1 || $value === 0;
	}

}
