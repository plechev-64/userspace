<?php

namespace USP\Admin\OptionsManager;

class OptionsGroup {

	public ?string $group_id = null;
	public ?string $title = null;
	public ?string $option_name = null;
	public array $options = [];
	public bool $extend = false;
	public array $option_values = array();

	public function __construct( string $group_id, array $args, string $option_name ) {

		$this->group_id = $group_id;
		$this->option_name = $option_name;
		$this->option_values = $this->get_option( $this->option_name );

		if ( $args ) {
			$this->init_properties( $args );
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

	public function get_option( string $option_name ): array|string {
		return wp_unslash( get_site_option( $option_name ) );
	}

	public function get_value( string $option, mixed $default = null, string $group = null, bool $local = false ): mixed {

		if ( $group ) {
			if ( isset( $this->option_values[ $group ][ $option ] ) ) {
				if ( $this->option_values[ $group ][ $option ] || is_numeric( $this->option_values[ $group ][ $option ] ) ) {
					return $this->option_values[ $group ][ $option ];
				}
			}
		} else if ( $local ) {
			return $this->get_option( $option );
		} else {
			if ( isset( $this->option_values[ $option ] ) ) {
				if ( $this->option_values[ $option ] || is_numeric( $this->option_values[ $option ] ) ) {
					return $this->option_values[ $option ];
				}
			}
		}

		return $default;
	}

	public function add_options( array $options ): void {
		foreach ( $options as $option ) {
			if($option){
				$this->add_option( $option );
			}
		}
	}

	public function add_option( array $option ): void {

		if ( ! isset( $option['slug'] ) ) {
			if ( isset( $option['type'] ) && $option['type'] == 'custom' ) {
				$option['slug'] = md5( current_time( 'mysql' ) );
			} else {
				return;
			}
		}

		$option_id = $option['slug'];
		$default   = $option['default'] ?? false;
		$group     = isset( $option['group'] ) && $option['group'] ? $option['group'] : false;
		$local     = isset( $option['local'] ) && $option['local'];

		if ( ! isset( $option['value'] ) ) {
			$option['value'] = $this->get_value( $option_id, $default, $group, $local );
		}

		if ( $group ) {
			$option['input_name'] = $this->option_name . '[' . $option['group'] . '][' . $option_id . ']';
		} else if ( $local ) {
			$option['input_name'] = 'local[' . $option_id . ']';
		} else {
			$option['input_name'] = $this->option_name . '[' . $option_id . ']';
		}

		$this->options[ $option_id ] = Option::setup_option( $option );

		if ( isset( $option['children'] ) ) {
			foreach ( $option['children'] as $parentValue => $childFields ) {

				if ( ! is_array( $childFields ) ) {
					continue;
				}

				foreach ( $childFields as $childField ) {

					$childField['parent'] = [
						'id'    => $option_id,
						'value' => $parentValue
					];

					$this->add_option( $childField );
				}
			}
		}
	}

	public function get_content(): ?string {

		if ( ! $this->options ) {
			return null;
		}

		$content = '<div id="options-group-' . esc_attr( $this->group_id ) . '" class="options-group ' . ( $this->extend ? 'extend-options' : '' ) . '" data-group="' . esc_attr( $this->group_id ) . '">';

		if ( $this->title ) {
			$content .= '<span class="usp-options-group-title">' . $this->title . '</span>';
		}

		foreach ( $this->options as $option ) {

			$args = [ 'classes' => [ 'usp-option' ] ];

			if ( $option->extend ) {
				$args['classes'][] = 'extend-options';
			}

			$content .= $option->get_field_html( $args );
		}

		$content .= '</div>';

		return $content;
	}

}
