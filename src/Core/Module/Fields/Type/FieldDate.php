<?php

namespace USP\Core\Module\Fields\Type;

use USP\Core\Module\Fields\FieldAbstract;

class FieldDate extends FieldAbstract {

	public bool $required = false;
	public ?string $placeholder = null;

	public function get_options(): array {
		return [
			[
				'slug'    => 'placeholder',
				'default' => $this->placeholder,
				'type'    => 'text',
				'title'   => __( 'Placeholder', 'userspace' )
			]
		];
	}

	public function get_input(): string {

		usp_datepicker_scripts();

		$this->class = 'usp-datepicker';

		return '<input type="text" ' . $this->get_class() . ' autocomplete="off" onclick="usp_show_datepicker(this);" title="' . __( 'Use the format', 'userspace' ) . ': yyyy-mm-dd" pattern="(\d{4}-\d{2}-\d{2})" ' . $this->get_required() . ' ' . $this->get_placeholder() . ' name="' . esc_attr( $this->input_name ) . '" id="' . esc_attr( $this->input_id ) . '" value="' . esc_attr( $this->value ) . '"/>';
	}

	public function get_filter_value(): string {
		return '<a href="' . $this->get_filter_url() . '" target="_blank">' . esc_html( $this->value ) . '</a>';
	}

	public function is_valid_value( $value ): bool {
		return $value === date( "Y-m-d", strtotime( $value ) );
	}

}
