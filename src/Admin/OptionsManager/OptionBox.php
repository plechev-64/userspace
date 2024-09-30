<?php

namespace USP\Admin\OptionsManager;

class OptionBox {

	public ?string $box_id = null;
	public ?string $title;
	public ?string $icon = 'fa-cog';
	public array $groups = [];
	public ?string $option_name;
	public bool $active = false;

	public function __construct( string $box_id, array $args, string $option_name ) {

		$this->box_id = $box_id;

		$this->option_name = $option_name;

		$this->init_properties( $args );

		if ( isset( $_GET['usp-options-box'] ) ) {
			$this->active = $this->box_id == $_GET['usp-options-box'];
		}
	}

	private function init_properties( array $args ): void {

		$properties = get_class_vars( get_class( $this ) );

		foreach ( $properties as $name => $val ) {
			if ( isset( $args[ $name ] ) ) {
				$this->$name = $args[ $name ];
			}
		}
	}

	public function add_group( string $group_id, array $args = [] ): OptionsGroup {
		$this->groups[ $group_id ] = new OptionsGroup( $group_id, $args, $this->option_name );

		return $this->group( $group_id );
	}

	public function isset_group( string $group_id ): bool {
		return isset( $this->groups[ $group_id ] );
	}

	public function group( string $group_id ): OptionsGroup {
		return $this->groups[ $group_id ];
	}

	public function add_options( array $options ): void {

		if ( ! $this->isset_group( 'general' ) ) {
			$this->add_group( 'general', [
				'title' => __( 'General settings', 'userspace' )
			] );
		}

		$this->group( 'general' )->add_options( $options );
	}

	public function get_content(): string {

		$content = '<div id="' . esc_attr( $this->box_id ) . '-options-box" class="options-box ' . ( $this->active ? 'active' : '' ) . '" data-box="' . esc_attr( $this->box_id ) . '">';

		foreach ( $this->groups as $group ) {

			$content .= $group->get_content();
		}

		$content .= '</div>';

		return $content;
	}

}
