<?php

class USP_Wizard {

	private $title;
	private $image;
	private $steps = array();
	private $current_step;
	private $stepsArgs = array();

	function __construct( $args = false ) {

		if ( $args ) {
			$this->init_properties( $args );
		}
	}

	function init_properties( $args ) {

		$properties = get_class_vars( get_class( $this ) );

		foreach ( $properties as $name => $val ) {
			if ( isset( $args[ $name ] ) ) {
				$this->$name = is_bool( $args[ $name ] ) ? ( bool ) $args[ $name ] : $args[ $name ];
			}
		}
	}

	function get_current_step() {

		if ( ! $this->steps ) {
			return false;
		}

		if ( ! empty( $_GET['step'] ) ) {
			$stepNum = intval( $_GET['step'] );
			$num     = 1;
			foreach ( $this->steps as $sid => $step ) {
				if ( $num == $stepNum ) {
					return $sid;
				}
				$num ++;
			}
		}

		foreach ( $this->steps as $id => $step ) {
			return $id;
		}
	}

	function get_wizard() {

		$id = $this->get_current_step();

		$content = '<div id="usp-wizard">';

		if ( $this->image ) {
			$content .= '<div class="wizard-image">';
			$content .= '<img src="' . $this->image . '">';
			$content .= '</div>';
		}

		if ( $this->title ) {
			$content .= '<div class="wizard-title">';
			$content .= $this->title;
			$content .= '</div>';
		}

		$content .= $this->get_navigation( 'numbers' );

		$content .= $this->get_step_content( $id );

		$content .= $this->get_navigation( 'links' );

		$content .= '<script>USP.Wizard = {steps: ' . wp_json_encode( $this->stepsArgs ) . '}</script>';

		$content .= '</div>';

		return $content;
	}

	function get_number_step( $id ) {

		if ( ! $this->steps ) {
			return false;
		}

		$num = 1;
		foreach ( $this->steps as $sid => $step ) {
			if ( $id == $sid ) {
				return $num;
			}
			$num ++;
		}

		return false;
	}

	function get_navigation( $type ) {

		$navi = new USP_Pager( array(
			'total'   => count( $this->steps ),
			'number'  => 1,
			'onclick' => 'usp_get_wizard_page',
			'key'     => 'step'
		) );

		return $navi->get_pager( $type );
	}

	function get_step_content( $id ) {
		$step = $this->get_step( $id );
		if ( ! $step ) {
			return false;
		}

		return $step->get_content();
	}

	function get_step( $id ) {
		return isset( $this->steps[ $id ] ) ? $this->steps[ $id ] : false;
	}

	function add_step( $args ) {
		$this->stepsArgs[] = $args;
		$this->steps[]     = new USP_Wizard_Step( $args );
	}

}
