<?php

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
			],
			[
				'type'   => 'text',
				'slug'   => 'pattern',
				'title'  => __( 'Phone mask', 'userspace' ),
				'notice' => __( 'Example: 8\([0-9]{3}\)[0-9]{3}-[0-9]{2}-[0-9]{2} Result: 8(900)123-45-67', 'userspace' ),
			]
		];
	}

	function get_input() {
		return '<input type="' . esc_attr( $this->type ) . '" ' . $this->get_pattern() . ' ' . $this->get_maxlength() . ' ' . $this->get_required() . ' ' . $this->get_placeholder() . ' ' . $this->get_class() . ' name="' . esc_attr( $this->input_name ) . '" id="' . esc_attr( $this->input_id ) . '" value=\'' . esc_attr( $this->value ) . '\'/>';
	}

}
