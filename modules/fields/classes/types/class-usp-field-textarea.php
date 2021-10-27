<?php

class USP_Field_TextArea extends USP_Field_Abstract {

	public $required;
	public $placeholder;
	public $maxlength;

	function __construct( $args ) {
		parent::__construct( $args );
	}

	function get_options() {

		return [
			[
				'slug'    => 'placeholder',
				'default' => $this->placeholder,
				'type'    => 'text',
				'title'   => __( 'Placeholder', 'userspace' )
			],
			[
				'slug'    => 'maxlength',
				'default' => $this->maxlength,
				'type'    => 'number',
				'title'   => __( 'Maxlength', 'userspace' ),
				'notice'  => __( 'Maximum number of symbols per field', 'userspace' )
			]
		];
	}

	function get_input() {
		return '<textarea name="' . esc_attr( $this->input_name ) . '" ' . $this->get_maxlength() . ' ' . $this->get_required() . ' ' . $this->get_placeholder() . ' ' . $this->get_class() . ' id="' . esc_attr( $this->input_id ) . '" rows="5" cols="50">' . esc_textarea( $this->value ) . '</textarea>';
	}

	function get_value() {

		if ( ! $this->value ) {
			return false;
		}

		return nl2br( $this->value );
	}

	function is_valid_value( $value ) {

		if ( empty( $this->maxlength ) ) {
			return true;
		}

		return mb_strlen( $value ) <= $this->maxlength;
	}
}
