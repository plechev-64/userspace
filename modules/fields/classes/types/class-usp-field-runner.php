<?php

class USP_Field_Runner extends USP_Field_Abstract {

	public $value_min = 0;
	public $value_max = 100;
	public $value_step = 1;
	public $unit;

	function __construct( $args ) {
		parent::__construct( $args );
	}

	function get_options() {

		$options = array(
			array(
				'slug'        => 'unit',
				'default'     => $this->unit,
				'placeholder' => __( 'For example: km or pcs', 'userspace' ),
				'type'        => 'text',
				'title'       => __( 'Unit', 'userspace' )
			),
			array(
				'slug'    => 'value_min',
				'default' => $this->value_min,
				'type'    => 'number',
				'title'   => __( 'Min', 'userspace' ),
			),
			array(
				'slug'    => 'value_max',
				'default' => $this->value_max,
				'type'    => 'number',
				'title'   => __( 'Max', 'userspace' ),
			),
			array(
				'slug'    => 'value_step',
				'default' => $this->value_step,
				'type'    => 'number',
				'title'   => __( 'Step', 'userspace' ),
			)
		);

		return $options;
	}

	function get_input() {

		usp_slider_scripts();

		$content = '<div id="usp-runner-' . $this->rand . '" class="usp-runner usp-jogger usp-runner-' . $this->rand . ' usps usps__ai-center">';

		$content .= '<span class="usp-runner-value usp-jogger-value"><span></span>';
		if ( $this->unit ) {
			$content .= ' ' . $this->unit;
		}
		$content .= '</span>';

		$content .= '<div class="usp-runner-box usp-jogger-box usps__relative usps__radius-3"></div>';
		$content .= '<input type="hidden" class="usp-runner-field" id="' . $this->input_id . '" data-idrand="' . $this->rand . '" name="' . $this->input_name . '" value="' . $this->value_min . '">';
		$content .= '</div>';

		$init = 'usp_init_runner(' . json_encode( array(
				'id'    => $this->rand,
				'value' => $this->value ?: 0,
				'min'   => $this->value_min,
				'max'   => $this->value_max,
				'step'  => $this->value_step
			) ) . ');';

		if ( ! usp_is_ajax() ) {
			$content .= '<script>jQuery(window).on("load", function() {' . $init . '});</script>';
		} else {
			$content .= '<script>' . $init . '</script>';
		}

		return $content;
	}

	function get_value() {

		if ( is_null( $this->value ) || $this->value == '' ) {
			return false;
		}

		if ( $this->unit ) {
			return $this->value . ' ' . $this->unit;
		}

		return $this->value;
	}

	function get_filter_value() {
		$value = '<a href="' . $this->get_filter_url() . '" target="_blank">' . $this->value . '</a>';

		if ( $this->unit ) {
			$value .= ' ' . $this->unit;
		}

		return $value;
	}

	function is_valid_value( $value ) {

		if ( ! is_numeric( $value ) ) {
			return false;
		}

		if ( ! empty( $this->value_max ) && ( $value > $this->value_max ) ) {
			return false;
		}

		if ( ! empty( $this->value_min ) && ( $value < $this->value_min ) ) {
			return false;
		}

		$max_precision = strlen( $this->value_step ) - strrpos( $this->value_step, '.' ) - 1;

		[ , $value_fraction ] = explode( '.', $value );

		if ( ! empty( $value_fraction ) && strlen( $value_fraction ) > $max_precision ) {
			return false;
		}

		return true;
	}

}
