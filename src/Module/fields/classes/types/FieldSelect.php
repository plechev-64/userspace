<?php

class FieldSelect extends FieldAbstract {
	
	public bool $empty_first = false;
	public array $values = [];
	public bool $key_in_data = false;

	public function __construct( array $args ) {
		parent::__construct( $args );
	}

	public function get_options(): array {
		return [
			[
				'slug'    => 'empty_first',
				'default' => $this->empty_first,
				'type'    => 'text',
				'title'   => __( 'First value', 'userspace' ),
				'notice'  => __( 'Name of the first blank value, for example: "Not selected"', 'userspace' )
			],
			[
				'slug'    => 'values',
				'default' => $this->values,
				'type'    => 'dynamic',
				'title'   => __( 'Specify options', 'userspace' ),
				'notice'  => __( 'Specify each option in a separate field', 'userspace' )
			]
		];
	}

	public function get_value(): null|string|int {

		if ( is_null( $this->value ) ) {
			return null;
		}

		if ( $this->value_in_key ) {
			return $this->value;
		}

		return $this->values[ $this->value ];
	}

	public function get_input(): string {

		$content = '<select ' . $this->get_required() . ' name="' . esc_attr( $this->input_name ) . '" id="' . esc_Attr( $this->input_id ) . '" ' . $this->get_class() . '>';

		if ( $this->empty_first ) {
			$content .= '<option value="">' . esc_html( $this->empty_first ) . '</option>';
		}

		if ( $this->values ) {
			foreach ( $this->values as $k => $value ) {

				$data = ( $this->key_in_data ) ? 'data-key="' . esc_attr( $k ) . '"' : '';

				if ( $this->value_in_key ) {
					$k = $value;
				}

				$content .= '<option ' . selected( $this->value, $k, false ) . ' ' . $data . ' value="' . esc_attr( $k ) . '">' . esc_html( $value ) . '</option>';
			}
		}

		$content .= '</select>';

		return $content;
	}

	public function get_filter_value(): string {
		return '<a href="' . esc_url( $this->get_filter_url() ) . '" target="_blank">' . esc_html( $this->value ) . '</a>';
	}

	public function is_valid_value( $value ): bool {

		if ( is_array( $value ) ) {
			return false;
		}

		$valid_values = $this->value_in_key ? $this->values : array_keys( $this->values );

		return in_array( $value, $valid_values );
	}

}
