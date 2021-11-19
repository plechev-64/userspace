<?php

class USP_Options_Box {

	public $box_id;
	public $title;
	public $icon = 'fa-cog';
	public $groups;
	public $option_name;
	public $active = false;

	function __construct( $box_id, $args, $option_name ) {

		$this->box_id = $box_id;

		$this->option_name = $option_name;

		$this->init_properties( $args );

		if ( isset( $_GET['usp-options-box'] ) ) {
			$this->active = $this->box_id == $_GET['usp-options-box'] ? true : false;
		}
	}

	function init_properties( $args ) {

		$properties = get_class_vars( get_class( $this ) );

		foreach ( $properties as $name => $val ) {
			if ( isset( $args[ $name ] ) ) {
				$this->$name = $args[ $name ];
			}
		}
	}

	function add_group( $group_id, $args = false ) {
		$this->groups[ $group_id ] = new USP_Options_Group( $group_id, $args, $this->option_name );

		return $this->group( $group_id );
	}

	function isset_group( $group_id ) {
		return isset( $this->groups[ $group_id ] );
	}

	function group( $group_id ) {
		return $this->groups[ $group_id ];
	}

	function add_options( $options ) {

		if ( ! $this->isset_group( 'general' ) ) {
			$this->add_group( 'general', [
				'title' => __( 'General settings', 'userspace' )
			] );
		}

		$this->group( 'general' )->add_options( $options );
	}

	function get_content() {

		$content = '<div id="' . esc_attr( $this->box_id ) . '-options-box" class="options-box ' . ( $this->active ? 'active' : '' ) . '" data-box="' . esc_attr( $this->box_id ) . '">';

		foreach ( $this->groups as $group ) {

			$content .= $group->get_content();
		}

		$content .= '</div>';

		return $content;
	}

}
