<?php

class USP_Field_MultiSelect extends USP_Field_Abstract {

	public $required;
	public $values;

	function __construct( $args ) {
		parent::__construct( $args );
	}

	function get_options() {

		return array(
			array(
				'slug'    => 'values',
				'default' => $this->values,
				'type'    => 'dynamic',
				'title'   => __( 'Specify options', 'userspace' ),
				'notice'  => __( 'Specify each option in a separate field', 'userspace' )
			)
		);
	}

	function get_input() {

		if ( ! $this->values ) {
			return false;
		}

		usp_multiselect_scripts();

		$this->value = ( $this->value ) ? $this->value : array();

		if ( ! is_array( $this->value ) ) {
			$this->value = array( $this->value );
		}

		$content = '<select ' . $this->get_required() . ' name="' . $this->input_name . '[]" id="' . $this->input_id . '" ' . $this->get_class() . ' multiple>';

		foreach ( $this->values as $k => $value ) {

			if ( $this->value_in_key ) {
				$k = $value;
			}

			$content .= '<option ' . selected( in_array( $k, $this->value ), true, false ) . ' value="' . trim( $k ) . '">' . $value . '</option>';
		}

		$content .= '</select>';

		$init = 'jQuery("#' . $this->input_id . '").multiselect({
				    search: true,
				    maxPlaceholderOpts: 5,
				    texts: {
				        placeholder: "' . __( 'Select some options', 'userspace' ) . '",
				        search: "' . __( 'Search', 'userspace' ) . '",
				        selectedOptions: " ' . __( 'selected', 'userspace' ) . '",
				    }
				});';

		if ( ! usp_is_ajax() ) {
			$content .= '<script>jQuery(window).on("load", function() {' . $init . '});</script>';
		} else {
			$content .= '<script>' . $init . '</script>';
		}

		return $content;
	}

	function get_value() {

		if ( ! $this->value ) {
			return false;
		}

		return implode( ', ', array_intersect_key( $this->values, array_flip( $this->value ) ) );
	}

	function get_filter_value() {

		$links = array();

		foreach ( $this->value as $val ) {

			if ( ! $val ) {
				continue;
			}

			$links[] = '<a href="' . $this->get_filter_url( $val ) . '" target="_blank">' . $val . '</a>';
		}

		return implode( ', ', $links );
	}

	function is_valid_value( $value ) {

		$valid_values = $this->value_in_key ? $this->values : array_keys( $this->values );

		return (bool) array_intersect( (array) $value, $valid_values );
	}

}
