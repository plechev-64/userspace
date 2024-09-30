<?php

namespace USP\Core\Module\Fields\Type;

use USP\Core\Module\Fields\FieldAbstract;

class FieldDynamic extends FieldAbstract {

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

		if ( ! $this->default ) {
			$this->default = '';
		}

		$content = '<div class="dynamic-values">';

		if ( $this->value && is_array( $this->value ) ) {
			$cnt = count( $this->value );
			foreach ( $this->value as $k => $val ) {

				$key = is_string( $k ) ? esc_attr( $k ) : '';

				$content .= '<span class="dynamic-value">';
				$content .= '<input type="text" ' . $this->get_required() . ' ' . $this->get_placeholder() . ' name="' . esc_attr( $this->input_name ) . '[' . $key . ']" value="' . esc_attr( $val ) . '"/>';
				if ( ! is_string( $k ) ) {
					if ( $cnt == ++ $k ) {
						$content .= '<a href="#" onclick="usp_add_dynamic_field(this);return false;"><i class="uspi fa-plus" aria-hidden="true"></i></a>';
					} else {
						$content .= '<a href="#" onclick="usp_remove_dynamic_field(this);return false;"><i class="uspi fa-minus" aria-hidden="true"></i></a>';
					}
				}
				$content .= '</span>';
			}
		} else {
			$content .= '<span class="dynamic-value">';
			$content .= '<input type="text" ' . $this->get_required() . ' ' . $this->get_placeholder() . ' name="' . esc_attr( $this->input_name ) . '[]" value="' . esc_attr( $this->default ) . '"/>';
			$content .= '<a href="#" onclick="usp_add_dynamic_field(this);return false;"><i class="uspi fa-plus" aria-hidden="true"></i></a>';
			$content .= '</span>';
		}

		$content .= '</div>';

		return $content;
	}

	public function get_value(): ?string {

		if ( ! $this->value ) {
			return null;
		}

		return esc_html( implode( ', ', $this->value ) );
	}

}
