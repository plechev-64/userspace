<?php

class USP_Tab {

	public $id;
	public $name = false;
	public $icon = 'fa-cog';
	public $public = 0;
	public $hidden = 0;
	public $counter = null;
	public $output = 'menu';
	public $supports = [];
	public $content = [];
	public $custom_tab = false;
	public $current_id = 0;
	public $url = false;
	public $onclick = false;

	function __construct( $tabData ) {

		$this->init_properties( $tabData );

		//$this->setup_subtabs();
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

	function setup_subtabs() {
		foreach ( $this->content as $k => $subtabData ) {
			$this->content[ $k ] = $this->new_subtab( $subtabData );
		}
	}

	function add_subtab( $subtabData ) {
		$this->content[] = $this->new_subtab( $subtabData );
	}

	function new_subtab( $subtabData ) {
		return new USP_Sub_Tab( wp_parse_args( $subtabData, [
			'id'        => $this->id,
			'name'      => $this->name,
			'icon'      => $this->icon,
			'parent_id' => $this->id
		] ) );
	}

	function set_prop( $propName, $value ) {
		$this->$propName = $value;
	}

	function is_prop( $propName ) {
		return isset( $this->$propName );
	}

	function get_prop( $propName ) {
		return $this->is_prop( $propName ) ? $this->$propName : false;
	}

	function isset_subtab( $subtab_id ) {

		if ( ! $this->content ) {
			return false;
		}

		foreach ( $this->content as $k => $subtab ) {
			if ( $subtab->id == $subtab_id ) {
				return $subtab;
			}
		}

		return false;
	}

	function subtab( $subtab_id = false ) {

		if ( ! $this->content ) {
			return false;
		}

		foreach ( $this->content as $k => $subtab ) {
			if ( ! $subtab_id || $subtab->id == $subtab_id ) {
				return $subtab;
			}
		}

		return false;
	}

	function is_active_tab() {

		$active = false;

		if ( isset( $_GET['tab'] ) ) {
			$active = ( $_GET['tab'] == $this->id ) ? true : false;
		} else {
			if ( USP()->tabs()->current_id == $this->id ) {
				$active = true;
			}
		}

		return $active;
	}

	function get_class_button() {

		$classes = apply_filters( 'usp_tab_class_button', [ 'usp-tab-button' ], $this->id );

		if ( in_array( 'dialog', $this->supports ) ) {
			$classes[] = 'usp-dialog';
			//$classes[]	 = 'usp-ajax';
		} else if ( in_array( 'ajax', $this->supports ) ) {
			//$classes[] = 'usp-ajax';
		}

		return $classes;
	}

	function get_button( $args = [] ) {

		$ajaxLoad = false;
		if ( isset( $this->supports ) ) {
			if ( in_array( 'ajax', $this->supports ) ) {
				$ajaxLoad = true;
			}
		}

		$onclick = $ajaxLoad ? 'usp_load_tab("' . $this->id . '", 0, this);return false;' : null;

		if ( $this->onclick ) {
			$onclick = $this->onclick;
		}

		$attr = wp_parse_args( $args, [
			'id'        => 'usp-tab__' . $this->id,
			'class'     => implode( ' ', $this->get_class_button() ),
			'label'     => $this->name,
			'icon'      => $this->icon,
			'counter'   => $this->counter,
			'href'      => $this->get_permalink(),
			'icon_mask' => 1,
			//'status'	 => $status,
			'onclick'   => $this->url ? false : $onclick
		] );

		return usp_get_button( $attr );
	}

	function get_permalink( $user_id = false ) {
		if ( ! $user_id ) {
			$user_id = USP()->office()->get_owner_id();
		}

		return $this->url ?: add_query_arg( [ 'tab' => $this->id ], usp_user_get_url( $user_id ) );
	}

	function is_access() {
		global $user_ID;

		if ( $this->public == 0 ) {
			if ( ! $user_ID || ! USP()->office()->is_owner( $user_ID ) ) {
				return false;
			}
		} else if ( $this->public == - 1 ) {
			if ( ! $user_ID || USP()->office()->is_owner( $user_ID ) ) {
				return false;
			}
		} else if ( $this->public == - 2 ) {
			if ( $user_ID && USP()->office()->is_owner( $user_ID ) ) {
				return false;
			}
		}

		return true;
	}

	function get_active_subtab_id() {

		if ( isset( $_GET['subtab'] ) ) {

			foreach ( $this->content as $k => $subtab ) {
				if ( $_GET['subtab'] == $subtab->id ) {
					return $subtab->id;
				}
			}
		}

		return $this->content[0]->id;
	}

	function get_menu() {

		if ( ! $this->content || count( $this->content ) < 2 ) {
			return false;
		}

		if ( ! $this->current_id ) {
			$this->current_id = $this->get_active_subtab_id();
		}

		$content = '<div class="usps usp-subtabs-menu">';

		foreach ( $this->content as $subtab ) {

			$content .= $subtab->get_button( $this->current_id == $subtab->id ? [ 'status' => 'active' ] : [] );
		}

		$content .= '</div>';

		return $content;
	}

	function get_content() {

		if ( ! $this->is_access() ) {
			return false;
		}

		if ( ! $this->current_id ) {
			$this->current_id = $this->get_active_subtab_id();
		}

		$subtab = $this->subtab( $this->current_id );

		$content = '<div id="usp-tab-content" class="usp-tab-' . esc_attr( $this->id ) . ' usps__relative usps__grow">';

		$content .= $this->get_menu();

		$content .= $subtab->get_content();

		$content .= '</div>';

		return $content;
	}

}
