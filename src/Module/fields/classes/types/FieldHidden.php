<?php

class FieldHidden extends FieldAbstract {

	public array $values = array();
	public ?string $placeholder = null;
	public ?string $pattern = null;

	public function __construct( array $args ) {
		parent::__construct( $args );
	}

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
			],
			[
				'slug'    => 'pattern',
				'default' => $this->pattern,
				'type'    => 'text',
				'title'   => __( 'Pattern', 'userspace' )
			]
		];
	}

	public function get_field_input(): string {
		return $this->get_input();
	}

	public function get_field_html( array $args = [] ): string {
		return $this->get_field_input();
	}

	public function get_input(): string {

		if ( $this->values && is_array( $this->values ) ) {

			$content = '';
			foreach ( $this->values as $value ) {
				$content .= '<input type="' . esc_attr( $this->type ) . '" ' . $this->get_class() . ' name="' . esc_attr( $this->input_name ) . '[]" value=\'' . esc_attr( $value ) . '\'/>';
			}

			return $content;
		}

		return '<input type="' . esc_attr( $this->type ) . '" ' . $this->get_pattern() . ' ' . $this->get_maxlength() . ' ' . $this->get_required() . ' ' . $this->get_placeholder() . ' ' . $this->get_class() . ' name="' . esc_attr( $this->input_name ) . '" id="' . esc_attr( $this->input_id ) . '" value=\'' . esc_attr( $this->value ) . '\'/>';
	}

}
