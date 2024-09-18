<?php

class Notice {

	public string $type = 'info';   // info,success,warning,error,simple
	public string $title = '';       // title text
	public string $text = '';       // text message
	public bool $text_center = true;     // true - text-align: center; false - left
	public bool $icon = true;     // left position icon; false - don't show, string - icon class. Example: 'fa-info'
	public string $class = '';       // additional class
	public bool $no_border = false;    // hide border color
	public string $cookie = '';       // unique cookie id
	public int $cookie_time = 30;       // lifetime cookie

	public function __construct( array $args ) {

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

	private function init_properties( array $args ) {

		$properties = get_class_vars( get_class( $this ) );

		foreach ( $properties as $name => $val ) {
			if ( isset( $args[ $name ] ) ) {
				$this->$name = $args[ $name ];
			}
		}
	}

	private function setup_class(): void {
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

	private function setup_icon(): void {

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

	public function get_notice(): ?string {

		if ( ! empty( $this->cookie ) && isset( $_COOKIE[ $this->cookie ] ) ) {
			return null;
		}

		return usp_get_include_template( 'usp-notice.php', false, [ 'notice' => $this, ] );
	}

}
