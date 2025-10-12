<?php

namespace USP\Core\Module\Fields\Type;

use USP\Core\Module\Fields\FieldAbstract;

class FieldTextArea extends FieldAbstract {
	
	public ?string $placeholder = null;

	public function get_options(): array {

		return [
			[
				'slug'    => 'placeholder',
				'default' => $this->placeholder,
				'type'    => 'text',
				'title'   => __( 'Placeholder', 'userspace' )
			],
			[
				'slug'    => 'maxlength',
				'default' => $this->maxlength,
				'type'    => 'number',
				'title'   => __( 'Maxlength', 'userspace' ),
				'notice'  => __( 'Maximum number of symbols per field', 'userspace' )
			]
		];
	}

	public function get_input(): string {
		return '<textarea name="' . esc_attr( $this->input_name ) . '" ' . $this->get_maxlength() . ' ' . $this->get_required() . ' ' . $this->get_placeholder() . ' ' . $this->get_class() . ' id="' . esc_attr( $this->input_id ) . '" rows="5" cols="50">' . esc_textarea( $this->value?? '' ) . '</textarea>';
	}

	public function get_value(): ?string {

		if ( ! $this->value ) {
			return null;
		}

		return nl2br( $this->value );
	}

	public function is_valid_value( $value ): bool {

		if ( empty( $this->maxlength ) ) {
			return true;
		}

		return mb_strlen( $value ) <= $this->maxlength;
	}
}
