<?php

class USP_Sub_Tab {

	public $id;
	public $parent_id;
	public $name = false;
	public $title = false;
	public $icon = 'fa-cog';
	public $supports = [];
	public $counter = null;
	public $callback = [];
	public $url = false;

	function __construct( $subtabData ) {
		$this->init_properties( $subtabData );
		$tab            = USP()->tabs()->tab( $this->parent_id );
		$this->supports = $tab->supports;
	}

	function init_properties( $args ) {

		$properties = get_class_vars( get_class( $this ) );

		foreach ( $properties as $name => $val ) {
			if ( ! isset( $args[ $name ] ) ) {
				continue;
			}
			$this->$name = $args[ $name ];
		}
	}

	function setup_prop( $propName, $value ) {
		$this->$propName = $value;
	}

	function is_prop( $propName ) {
		return isset( $this->$propName );
	}

	function get_prop( $propName ) {
		return $this->is_prop( $propName ) ? $this->$propName : false;
	}

	function get_permalink( $user_id = false ) {
		if ( ! $user_id ) {
			$user_id = USP()->office()->get_owner_id();
		}

		return add_query_arg( [ 'tab' => $this->parent_id, 'subtab' => $this->id ], usp_user_get_url( $user_id ) );
	}

	function get_button( $args = array() ) {

		$tab = USP()->tabs()->tab( $this->parent_id );

		$ajaxLoad = false;
		if ( isset( $tab->supports ) ) {
			if ( in_array( 'ajax', $tab->supports ) ) {
				$ajaxLoad = true;
			}
		}

		$attr = wp_parse_args( $args, [
			'id'      => 'usp-tab__' . esc_attr( $this->id ),
			'class'   => 'usp-subtab-button',
			'label'   => $this->name,
			'icon'    => $this->icon,
			'counter' => $this->counter,
			'href'    => $this->get_permalink(),
			'onclick' => $ajaxLoad ? 'usp_load_tab("' . esc_attr( $tab->id ) . '", "' . esc_attr( $this->id ) . '", this);return false;' : null
		] );

		return usp_get_button( $attr );
	}

	function get_content() {
		global $usp_tab;

		$usp_tab = $this;

		$title = $this->title ? $this->title : $this->name;

		$content = '<div id="usp-subtab-' . $this->id . '" class="usp-subtab-box">';

		$content .= '<div class="usp-subtab-title usps usps__nowrap usps__ai-center usps__line-1">';
		if ( $this->icon ) {
			$content .= '<i class="uspi ' . esc_attr( $this->icon ) . '" aria-hidden="true"></i> ';
		}
		$content .= '<span>' . apply_filters( 'usp_subtab_title', $title, $this->id ) . '</span>';
		$content .= '</div>';

		if ( $this->callback ) {

			if ( isset( $this->callback['args'] ) ) {
				$args = $this->callback['args'];
			} else {
				$args = array( USP()->office()->get_owner_id() );
			}

			$content .= '<div class="usp-subtab-content">';
			if ( function_exists( $this->callback['name'] ) ) {
				$content .= apply_filters( 'usp_tab_content', call_user_func_array( $this->callback['name'], $args ), $this->parent_id, $this->id );
			} else {
				$content .= usp_get_notice( [ 'text' => __( 'There was an error loading the tab. Function not found.', 'userspace' ) ] );
			}
			$content .= '</div>';
		}

		$content .= '</div>';

		return $content;
	}

}
