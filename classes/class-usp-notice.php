<?php

class USP_Notice {

	public $type = 'info';   // info,success,warning,error,simple
	public $title = '';       // title text
	public $text = '';       // text message
	public $text_center = true;     // true - text-align: center; false - left
	public $icon = true;     // left position icon; false - don't show, string - icon class. Example: 'fa-info'
	public $class = '';       // additional class
	public $no_border = false;    // hide border color
	public $cookie = '';       // unique cookie id
	public $cookie_time = 30;       // lifetime cookie

	function __construct( $args ) {

		if ( isset( $args['success'] ) ) {
			$args['type'] = 'success';
			$args['text'] = $args['success'];
		} else if ( isset( $args['error'] ) ) {
			$args['type'] = 'error';
			$args['text'] = $args['error'];
		}

		$this->init_properties( $args );

		$this->setup_icon();
		$this->setup_class();
	}

	function init_properties( $args ) {

		$properties = get_class_vars( get_class( $this ) );

		foreach ( $properties as $name => $val ) {
			if ( isset( $args[ $name ] ) ) {
				$this->$name = $args[ $name ];
			}
		}
	}

	function setup_class() {
		$center = ( $this->text_center ) ? 'usp-notice__text-center' : '';

		$classes = [
			'usp-notice',
			'usps__relative',
			'usps__line-normal',
			'usp-notice__type-' . $this->type,
			$center
		];

		if ( $this->class ) {
			array_unshift( $classes, $this->class );
		}

		if ( $this->no_border ) {
			$classes[] = 'usp-notice__no-border';
		}

		$this->class = implode( ' ', $classes );
	}

	function setup_icon() {

		if ( ! $this->icon ) {
			return;
		}

		if ( ! is_string( $this->icon ) ) {
			switch ( $this->type ) {
				case 'success':
					$this->icon = 'fa-check-circle';
					break;
				case 'warning':
					$this->icon = 'fa-exclamation-circle';
					break;
				case 'info':
					$this->icon = 'fa-info-circle';
					break;
				case 'error':
					$this->icon = 'fa-exclamation-triangle';
					break;
			}
		}
	}

	function get_notice() {

		if ( ! empty( $this->cookie ) && isset( $_COOKIE[ $this->cookie ] ) ) {
			return;
		}

		$content = usp_get_include_template( 'usp-notice.php', false, [ 'notice' => $this, ] );

		return $content;
	}

}
