<?php

class FieldText extends FieldAbstract {

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

	public function get_input(): string {
		return '<input type="' . esc_attr( $this->type ) . '" ' . $this->get_pattern() . ' ' . $this->get_maxlength() . ' ' . $this->get_required() . ' ' . $this->get_placeholder() . ' ' . $this->get_class() . ' name="' . esc_attr( $this->input_name ) . '" id="' . esc_attr( $this->input_id ) . '" value=\'' . esc_attr( $this->value ) . '\'/>';
	}

	public function get_value(): ?string {

		if ( ! $this->value ) {
			return null;
		}

		if ( $this->type == 'email' ) {
			return '<a rel="nofollow" target="_blank" href="mailto:' . esc_attr( $this->value ) . '">' . esc_html( $this->value ) . '</a>';
		}
		if ( $this->type == 'url' ) {
			return '<a rel="nofollow" target="_blank" href="' . esc_url( $this->value ) . '">' . esc_html( $this->value ) . '</a>';
		}

		return $this->value;
	}

	public function get_filter_value(): string {
		return '<a href="' . $this->get_filter_url() . '" target="_blank">' . esc_html( $this->value ) . '</a>';
	}

	public function is_valid_value( $value ): bool {

		if ( empty( $this->maxlength ) ) {
			return true;
		}

		return mb_strlen( $value ) <= $this->maxlength;
	}

}
