<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class-usp-custom-field-text
 *
 * @author Андрей
 */
class USP_Field_Range extends USP_Field_Abstract {

	public $value_min	 = 0;
	public $value_max	 = 100;
	public $value_step	 = 1;
	public $manual_input = 0;
	public $unit;

	function __construct( $args ) {
		parent::__construct( $args );
	}

	function get_options() {

		$options = array(
			array(
				'slug'			 => 'unit',
				'default'		 => $this->unit,
				'placeholder'	 => __( 'For example: km or pcs', 'usp' ),
				'type'			 => 'text',
				'title'			 => __( 'Unit', 'usp' )
			),
			array(
				'slug'		 => 'value_min',
				'value'		 => $this->value_min,
				'type'		 => 'number',
				'title'		 => __( 'Min', 'usp' ),
				'default'	 => 0
			),
			array(
				'slug'		 => 'value_max',
				'value'		 => $this->value_max,
				'type'		 => 'number',
				'title'		 => __( 'Max', 'usp' ),
				'default'	 => 100
			),
			array(
				'slug'		 => 'value_step',
				'value'		 => $this->value_step,
				'type'		 => 'number',
				'title'		 => __( 'Step', 'usp' ),
				'default'	 => 1
			),
			array(
				'slug'	 => 'manual_input',
				'value'	 => $this->manual_input,
				'type'	 => 'radio',
				'title'	 => __( 'Manual input', 'usp' ),
				'values' => array(
					__( 'Disable', 'usp' ),
					__( 'Enable', 'usp' )
				)
			)
		);

		return $options;
	}

	function get_input() {

		usp_slider_scripts();

		$valMin	 = $this->value ? $this->value[0] : $this->value_min;
		$valMax	 = $this->value ? $this->value[1] : $this->value_max;

		$content = '<div id="usp-range-' . $this->rand . '" class="usp-range">';

		if ( $this->manual_input ) {
			$content .= '<span class="usp-range-value manual-input">';
			$content .= '<input type="number" min="' . $this->value_min . '" max="' . $this->value_max . '" class="usp-range-min range-value" data-index="0" name="' . $this->input_name . '[]" value="' . $valMin . '">';
			$content .= '<span class="value-separator"> - </span>';
			$content .= '<input type="number" min="' . $this->value_min . '" max="' . $this->value_max . '" class="usp-range-max range-value" data-index="1" name="' . $this->input_name . '[]" value="' . $valMax . '">';
			$content .= '</span>';
		} else {
			$content .= '<input type="hidden" class="usp-range-min" name="' . $this->input_name . '[]" value="' . $this->value_min . '">';
			$content .= '<input type="hidden" class="usp-range-max" name="' . $this->input_name . '[]" value="' . $this->value_max . '">';
			$content .= '<span class="usp-range-value no-input"><span>' . (implode( ' - ', array( $valMin, $valMax ) )) . '</span>';
		}

		if ( $this->unit )
			$content .= ' ' . $this->unit;

		$content .= '</span>';

		$content .= '<div class="usp-range-box"></div>';

		$content .= '</div>';

		$init = 'usp_init_range(' . json_encode( array(
				'id'	 => $this->rand,
				'values' => $this->value ? $this->value : array( $this->value_min, $this->value_max ),
				'min'	 => $this->value_min,
				'max'	 => $this->value_max,
				'step'	 => $this->value_step,
				'manual' => $this->manual_input,
			) ) . ');';

		if ( ! usp_is_ajax() ) {
			$content .= '<script>jQuery(window).on("load", function() {' . $init . '});</script>';
		} else {
			$content .= '<script>' . $init . '</script>';
		}

		return $content;
	}

	function get_value() {

		if ( ! $this->value )
			return false;

		$minValue	 = $this->value[0];
		$maxValue	 = $this->value[1];

		if ( $this->unit ) {
			$minValue .= ' ' . $this->unit;
			$maxValue .= ' ' . $this->unit;
		}

		return __( 'from', 'usp' ) . ' ' . $minValue . ' ' . __( 'for', 'usp' ) . ' ' . $maxValue;
	}

}
