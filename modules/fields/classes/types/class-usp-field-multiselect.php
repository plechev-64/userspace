<?php

class USP_Field_MultiSelect extends USP_Field_Abstract {

	public $required;
	public $values;

	function __construct( $args ) {
		parent::__construct( $args );
	}

	function get_options() {

		return [
			[
				'slug'    => 'values',
				'default' => $this->values,
				'type'    => 'dynamic',
				'title'   => __( 'Specify options', 'userspace' ),
				'notice'  => __( 'Specify each option in a separate field', 'userspace' )
			]
		];
	}

	function get_input() {

		if ( ! $this->values ) {
			return false;
		}

		usp_multiselect_scripts();

		$this->value = $this->value ?: [];

		if ( ! is_array( $this->value ) ) {
			$this->value = [ $this->value ];
		}

		$content = '<select ' . $this->get_required() . ' name="' . esc_attr( $this->input_name ) . '[]" id="' . esc_attr( $this->input_id ) . '" ' . $this->get_class() . ' multiple>';

		foreach ( $this->values as $k => $value ) {

			if ( $this->value_in_key ) {
				$k = $value;
			}

			$content .= '<option ' . selected( in_array( $k, $this->value ), true, false ) . ' value="' . esc_attr( trim( $k ) ) . '">' . esc_html( $value ) . '</option>';
		}

		$content .= '</select>';

		$init = 'jQuery("#' . esc_js( $this->input_id ) . '").multiselect({
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

		if ( $this->value_in_key ) {
			return esc_html( implode( ', ', array_intersect( $this->value, $this->values ) ) );
		}

		return esc_html( implode( ', ', array_intersect_key( $this->values, array_flip( $this->value ) ) ) );
	}

	function get_filter_value() {

		$links = [];

		foreach ( $this->value as $val ) {

			if ( ! $val ) {
				continue;
			}

			$links[] = '<a href="' . esc_url( $this->get_filter_url( $val ) ) . '" target="_blank">' . esc_html( $val ) . '</a>';
		}

		return implode( ', ', $links );
	}

	function is_valid_value( $value ) {

		$valid_values = $this->value_in_key ? $this->values : array_keys( $this->values );

		return (bool) array_intersect( (array) $value, $valid_values );
	}

}
