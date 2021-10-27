<?php

class USP_Field_Text extends USP_Field_Abstract {

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
				'slug'    => 'pattern',
				'default' => $this->pattern,
				'type'    => 'text',
				'title'   => __( 'Pattern', 'userspace' )
			]
		];
	}

	function get_input() {
		return '<input type="' . esc_attr( $this->type ) . '" ' . $this->get_pattern() . ' ' . $this->get_maxlength() . ' ' . $this->get_required() . ' ' . $this->get_placeholder() . ' ' . $this->get_class() . ' name="' . esc_attr( $this->input_name ) . '" id="' . esc_attr( $this->input_id ) . '" value=\'' . esc_attr( $this->value ) . '\'/>';
	}

	function get_value() {

		if ( ! $this->value ) {
			return false;
		}

		if ( $this->type == 'email' ) {
			return '<a rel="nofollow" target="_blank" href="mailto:' . esc_attr( $this->value ) . '">' . esc_html( $this->value ) . '</a>';
		}
		if ( $this->type == 'url' ) {
			return '<a rel="nofollow" target="_blank" href="' . esc_url( $this->value ) . '">' . esc_html( $this->value ) . '</a>';
		}

		return $this->value;
	}

	function get_filter_value() {
		return '<a href="' . $this->get_filter_url() . '" target="_blank">' . esc_html( $this->value ) . '</a>';
	}

	function is_valid_value( $value ) {

		if ( empty( $this->maxlength ) ) {
			return true;
		}

		return mb_strlen( $value ) <= $this->maxlength;
	}

}
