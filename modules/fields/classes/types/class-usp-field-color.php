<?php

class USP_Field_Color extends USP_Field_Abstract {
	function __construct( $args ) {
		parent::__construct( $args );
	}

	function get_input() {

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'wp-color-picker' );

		$content = '<input type="text" ' . $this->get_class() . ' name="' . $this->input_name . '" id="' . $this->input_id . '" value="' . $this->value . '"/>';

		$init = 'usp_init_color("' . $this->input_id . '",' . json_encode( array(
				'defaultColor' => $this->value
			) ) . ')';

		if ( ! usp_is_ajax() ) {
			$content .= '<script>jQuery(window).on("load", function() {' . $init . '});</script>';
		} else {
			$content .= '<script>' . $init . '</script>';
		}

		return $content;
	}

}
