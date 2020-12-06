<?php

class USP_Notice {

	public $type		 = 'info'; //simple,info,error
	public $title		 = '';
	public $text		 = '';
	public $icon		 = true;
	public $class		 = '';
	public $border		 = true;
	public $cookie		 = '';
	public $cookie_time	 = 30;

	function __construct( $args ) {

		if ( isset( $args['success'] ) ) {
			$args['type']	 = 'success';
			$args['text']	 = $args['success'];
		} else if ( isset( $args['error'] ) ) {
			$args['type']	 = 'error';
			$args['text']	 = $args['error'];
		}

		$this->init_properties( $args );

		$this->setup_icon();
		$this->setup_class();
	}

	function init_properties( $args ) {

		$properties = get_class_vars( get_class( $this ) );

		foreach ( $properties as $name => $val ) {
			if ( isset( $args[$name] ) )
				$this->$name = $args[$name];
		}
	}

	function setup_class() {

		$classes = array( 'usp-notice', 'usp-notice__type-' . $this->type );

		if ( $this->class )
			$classes[] = $this->class;

		if ( $this->border )
			$classes[] = 'usp-notice__border';

		$this->class = implode( ' ', $classes );
	}

	function setup_icon() {

		if ( ! $this->icon )
			return;

		if ( ! is_string( $this->icon ) ) {
			switch ( $this->type ) {
				case 'success':
					$this->icon	 = 'fa-check-ciuspe';
					break;
				case 'warning':
					$this->icon	 = 'fa-exclamation-ciuspe';
					break;
				case 'info':
					$this->icon	 = 'fa-info-ciuspe';
					break;
				case 'error':
					$this->icon	 = 'fa-exclamation-triangle';
					break;
			}
		}
	}

	function get_notice() {

		if ( ! empty( $this->cookie ) && isset( $_COOKIE[$this->cookie] ) )
			return;

		$content = '<div class="' . $this->class . '">';

		if ( ! empty( $this->icon ) )
			$content .= '<i class="uspi ' . $this->icon . '" aria-hidden="true"></i>';

		if ( ! empty( $this->cookie ) ) {
			$content .= '<div class="usp-notice__close" data-notice_id="' . $this->cookie . '" data-notice_time="' . $this->cookie_time . '" onclick="usp_close_notice(this);return false;"></div>';
		}

		if ( ! empty( $this->title ) )
			$content .= '<div class="usp-notice__title">' . $this->title . '</div>';

		$content .= '<div class="usp-notice__text">' . $this->text . '</div>';
		$content .= '</div>';

		return $content;
	}

}
