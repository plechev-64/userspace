<?php

namespace USP\Core\Module\Fields\Type;

use USP\Core\Module\Fields\FieldAbstract;

class FieldNumber extends FieldAbstract {

	public ?string $placeholder = null;
	public ?int $value_max = null;
	public ?int $value_min = null;
	public ?int $value_step = null;

	public function get_options(): array {

		return [
			[
				'slug'    => 'placeholder',
				'default' => $this->placeholder,
				'type'    => 'text',
				'title'   => __( 'Placeholder', 'userspace' )
			],
			[
				'slug'    => 'value_min',
				'default' => $this->value_min,
				'type'    => 'number',
				'title'   => __( 'Min', 'userspace' ),
			],
			[
				'slug'    => 'value_max',
				'default' => $this->value_max,
				'type'    => 'number',
				'title'   => __( 'Max', 'userspace' ),
			],
			[
				'slug'    => 'value_step',
				'default' => $this->value_step,
				'type'    => 'select',
				'title'   => __( 'Step', 'userspace' ),
				'values'  => [ '1' => 1, '0.1' => 0.1, '0.01' => 0.01, '0.001' => 0.001, '0.0001' => 0.0001 ]
			],
		];
	}

	public function get_input(): string {
		return '<input type="' . esc_attr( $this->type ) . '" ' . $this->get_min() . ' ' . $this->get_max() . ' ' . $this->get_required() . ' ' . $this->get_placeholder() . ' ' . $this->get_class() . ' name="' . esc_attr( $this->input_name ) . '" id="' . esc_attr( $this->input_id ) . '" value=\'' . esc_attr( $this->value ) . '\' step=\'' . esc_attr( $this->value_step ) . '\'/>';
	}

	public function get_filter_value(): string {
		return '<a href="' . esc_url( $this->get_filter_url() ) . '" target="_blank">' . esc_html( $this->value ) . '</a>';
	}

	public function is_valid_value( mixed $value ): bool {

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
