<?php

class USP_Field_Date extends USP_Field_Abstract {

	public $required;
	public $placeholder;

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
			)
		);
	}

	function get_input() {

		usp_datepicker_scripts();

		$this->classes = 'usp-datepicker';

		$content = '<input type="text" ' . $this->get_class() . ' autocomplete="off" onclick="usp_show_datepicker(this);" title="' . __( 'Use the format', 'userspace' ) . ': yyyy-mm-dd" pattern="(\d{4}-\d{2}-\d{2})" ' . $this->get_required() . ' ' . $this->get_placeholder() . ' name="' . $this->input_name . '" id="' . $this->input_id . '" value="' . $this->value . '"/>';

		return $content;
	}

	function get_filter_value() {
		return '<a href="' . $this->get_filter_url() . '" target="_blank">' . $this->value . '</a>';
	}

	function is_valid_value( $value ) {
		return $value === date( "Y-m-d", strtotime( $value ) );
	}

}
