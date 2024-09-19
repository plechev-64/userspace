<?php

class FieldSwitch extends FieldAbstract {

	public array $values = [];
	public array $text = [
		'on' => null,
		'off' => null
	];

	public function __construct( $args ) {
		parent::__construct( $args );
	}

	public function get_options(): array {
		return [
			[
				'slug'    => 'switch',
				'default' => $this->values,
			]
		];
	}

	public function get_value(): ?string {

		if ( is_null( $this->value ) ) {
			return null;
		}

		return $this->value ? $this->text['on'] : $this->text['off'];
	}

	public function get_input(): ?string {

		if ( ! $this->slug ) {
			return null;
		}

		$input_id = $this->input_id . '-' . $this->rand;

		$data_off = ( $this->text['off'] ) ? 'data-off="' . esc_attr( $this->text['off'] ) . '"' : '';
		$data_on  = ( $this->text['on'] ) ? 'data-on="' . esc_attr( $this->text['on'] ) . '"' : '';

		$content = '<label class="usp-switch-box usps__relative" for="' . esc_attr( $input_id ) . '">';
		$content .= '<input type="hidden" class="switch-field-hidden" id="' . esc_attr( $this->input_id ) . '" value="' . esc_attr( $this->value ) . '" name="' . esc_attr( $this->input_name ) . '">';
		$content .= '<input type="checkbox" ' . checked( $this->value, 1, false ) . ' id="' . esc_attr( $input_id ) . '" ' . $this->get_class() . ' value="1" onclick="this.previousSibling.value=1-this.previousSibling.value"/> ';
		$content .= '<span class="usp-switch-label usps__relative" ' . $data_off . ' ' . $data_on . '></span>';
		$content .= '<span class="usp-switch-handle"></span>';
		$content .= '</label>';

		return $content;
	}

	public function is_valid_value( $value ): bool {
		return $value === 1 || $value === 0;
	}

}
