<?php

class FieldPhone extends FieldAbstract {

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
				'type'   => 'text',
				'slug'   => 'pattern',
				'title'  => __( 'Phone mask', 'userspace' ),
				'notice' => __( 'Example: 8\([0-9]{3}\)[0-9]{3}-[0-9]{2}-[0-9]{2} Result: 8(900)123-45-67', 'userspace' ),
			]
		];
	}

	public function get_input(): string {
		return '<input type="' . esc_attr( $this->type ) . '" ' . $this->get_pattern() . ' ' . $this->get_maxlength() . ' ' . $this->get_required() . ' ' . $this->get_placeholder() . ' ' . $this->get_class() . ' name="' . esc_attr( $this->input_name ) . '" id="' . esc_attr( $this->input_id ) . '" value=\'' . esc_attr( $this->value ) . '\'/>';
	}

}
