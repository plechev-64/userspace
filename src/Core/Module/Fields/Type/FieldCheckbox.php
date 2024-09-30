<?php

namespace USP\Core\Module\Fields\Type;

use USP\Core\Module\Fields\FieldAbstract;

class FieldCheckbox extends FieldAbstract {

	public bool $required = false;
	public array $values = [];
	public string $display = 'inline';
	public bool $check_all = false;

	public function get_options(): array {

		return [
			[
				'slug'    => 'values',
				'default' => $this->values,
				'type'    => 'dynamic',
				'title'   => __( 'Specify options', 'userspace' ),
				'notice'  => __( 'Specify each option in a separate field', 'userspace' )
			]
		];
	}

	public function get_value(): ?string {

		if ( ! $this->value ) {
			return null;
		}

		if ( $this->value_in_key ) {
			return implode( ', ', array_intersect( $this->value, $this->values ) );
		}

		return implode( ', ', array_intersect_key( $this->values, array_flip( $this->value ) ) );
	}

	public function get_filter_value(): string {

		$links = [];

		foreach ( $this->value as $val ) {

			if ( ! $val ) {
				continue;
			}

			$links[] = '<a href="' . esc_url( $this->get_filter_url( $val ) ) . '" target="_blank">' . esc_html( $val ) . '</a>';
		}

		return implode( ', ', $links );
	}

	public function get_input(): ?string {

		if ( ! $this->values ) {
			return null;
		}

		$currentValues = ( is_array( $this->value ) ) ? $this->value : [];

		$this->class = ( $this->required ) ? 'required-checkbox' : '';

		$content = '';

		if ( $this->check_all ) {

			$content .= '<div class="checkbox-manager">';

			$content .= usp_get_button( [
				'label'   => __( 'To mark all', 'userspace' ),
				'onclick' => 'return usp_check_all_actions_manager("' . esc_js( $this->input_name ) . '[]",this);return false;',
			] );

			$content .= usp_get_button( [
				'label'   => __( 'To delete all marks', 'userspace' ),
				'onclick' => 'return usp_uncheck_all_actions_manager("' . esc_js( $this->input_name ) . '[]",this);return false;',
			] );

			$content .= '</div>';
		}

		foreach ( $this->values as $k => $value ) {

			if ( $this->value_in_key ) {
				$k = $value;
			}

			$checked  = checked( in_array( $k, $currentValues ), true, false );
			$input_id = $this->input_id . '_' . $k . $this->rand;

			$content .= '<span class="usp-checkbox-box checkbox-display-' . esc_attr( $this->display ) . ' usps__inline usps__relative">';
			$content .= '<input ' . $this->get_required() . ' ' . $checked . ' id="' . esc_attr( $input_id ) . '" type="checkbox" ' . $this->get_class() . ' name="' . esc_attr( $this->input_name ) . '[]" value="' . esc_attr( trim( $k ) ) . '"> ';
			$content .= '<label class="usp-label usps usps__ai-center usps__no-select" for="' . esc_attr( $input_id ) . '">';
			$content .= esc_html( $value );
			$content .= '</label>';
			$content .= '</span>';
		}

		return $content;
	}

	public function get_field_input(): string {
		$content  = parent::get_field_input();
		$function = 'usp_init_update_requared_checkbox();';
		if ( ! usp_is_ajax() ) {
			$content .= '<script>jQuery(window).on("load", function() {' . $function . '});</script>';
		} else {
			$content .= '<script>' . $function . '</script>';
		}

		return $content;
	}

	public function is_valid_value( $value ): bool {

		if ( ! is_array( $value ) ) {
			return false;
		}

		$valid_values = $this->value_in_key ? $this->values : array_keys( $this->values );

		return (bool) array_intersect( $value, $valid_values );
	}

}
