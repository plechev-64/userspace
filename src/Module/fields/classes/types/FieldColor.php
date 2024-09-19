<?php

class FieldColor extends FieldAbstract {

	public function __construct( array $args ) {
		parent::__construct( $args );
	}

	public function get_input(): string {

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'wp-color-picker' );

		$content = '<input type="text" ' . $this->get_class() . ' name="' . esc_attr( $this->input_name ) . '" id="' . esc_attr( $this->input_id ) . '" value="' . esc_attr( $this->value ) . '"/>';

		$init = 'usp_init_color("' . esc_js( $this->input_id ) . '",' . json_encode( [
				'defaultColor' => esc_js( $this->value )
			] ) . ')';

		if ( ! usp_is_ajax() ) {
			$content .= '<script>jQuery(window).on("load", function() {' . $init . '});</script>';
		} else {
			$content .= '<script>' . $init . '</script>';
		}

		return $content;
	}

	public function is_valid_value( $value ): bool {
		return (bool) sanitize_hex_color( $value );
	}

}
