<?php

namespace USP\Core;

class Button {

	public ?string $id = null;
	public ?string $onclick = null;
	public string $href = 'javascript:void(0);';
	public array|string $class = [];
	public string $type = 'primary'; // clear, simple, primary
	public ?string $style = null;
	public ?string $icon = null; // for example: fa-car
	public string $icon_align = 'left'; // left or right position
	public ?string $icon_mask = null;  // 1 - is mask on icon
	public ?string $label = null;   // text on button
	public ?string $title = null;   // title attribute
	public ?int $counter = null; // number
	public ?string $content = null;
	public ?string $avatar = null;  // avatar button
	public bool $avatar_circle= false; // round avatar
	public array $data = [];
	public bool $submit = false;
	public ?string $status = null;  // state of the button: loading, disabled, active
	public string $size = 'standard';   // small, standard, medium, large, big
	public array $attrs = [];
	public bool $fullwidth = false;  // 1 - is fullwidth button
	public bool $inset = false;

	public function __construct( $args ) {

		if ( ! isset( $args['title'] ) && isset( $args['label'] ) ) {
			$args['title'] = $args['label'];
		}

		$this->init_properties( $args );

		$this->setup_class();

	}

	private function init_properties( $args ): void {

		$properties = get_class_vars( get_class( $this ) );

		foreach ( $properties as $name => $val ) {
			if ( isset( $args[ $name ] ) & ! empty( $args[ $name ] ) ) {
				$this->$name = $args[ $name ];
			}
		}
	}

	private function setup_attrs(): void {
		$this->class = array_reverse( $this->class );

		$this->attrs['href']    = $this->href;
		$this->attrs['title']   = $this->title;
		$this->attrs['onclick'] = $this->onclick;
		$this->attrs['style']   = $this->style;
		$this->attrs['id']      = $this->id;
		$this->attrs['class']   = is_array( $this->class ) ? implode( ' ', $this->class ) : $this->class;

		if ( $this->submit && ! $this->onclick ) {
			$this->attrs['onclick'] = 'usp_submit_form(this);return false;';
		}

		if ( $this->data ) {

			foreach ( $this->data as $k => $value ) {
				if ( ! $value ) {
					continue;
				}
				$this->attrs[ 'data-' . $k ] = $value;
			}
		}
	}

	private function setup_class(): void {

		if ( $this->class && ! is_array( $this->class ) ) {
			$this->class = [ $this->class, 'usp-bttn' ];
		} else {
			$this->class[] = 'usp-bttn';
		}

		if ( $this->icon ) {

			if ( 'right' == $this->icon_align && $this->label ) {

				if ( ! $this->counter ) {
					// only text & icon right
					$this->class[] = 'usp-bttn__mod-text-rico';
				} else if ( ! $this->avatar ) {
					// text & icon right & counter
					$this->class[] = 'usp-bttn__mod-text-rico-count';
				}
			} else if ( ! $this->counter && ! $this->avatar && ! $this->label ) {
				// only icon
				$this->class[] = 'usp-bttn__mod-only-icon';
			}

			if ( $this->icon_mask ) {
				$this->class[] = 'usp-bttn__ico-mask';
			}
		}

		$this->class[] = 'usp-bttn__type-' . $this->type;

		if ( $this->size ) {
			$this->class[] = 'usp-bttn__size-' . $this->size;
		}

		if ( $this->status ) {
			$this->class[] = 'usp-bttn__' . $this->status;
		}

		if ( $this->fullwidth ) {
			$this->class[] = 'usp-bttn__fullwidth';
		}

		if ( $this->inset ) {
			$this->class[] = 'usp-bttn__inset';
		}

		if ( $this->avatar_circle ) {
			$this->class[] = 'usp-bttn__ava_circle usps__radius-50';
		}

		$this->class = array_reverse( $this->class );
	}

	private function parse_attrs(): string {

		$attrs = [];
		foreach ( $this->attrs as $name => $value ) {
			if ( ! $value ) {
				continue;
			}
			$attrs[] = $name . '=\'' . $value . '\'';
		}

		return implode( ' ', $attrs );
	}

	private function get_icon(): string {
		return sprintf( '<i class="usp-bttn__ico usp-bttn__ico-%1$s uspi %2$s"></i>', $this->icon_align, $this->icon );
	}

	private function get_avatar(): string {
		return sprintf( '<span class="usp-bttn__ava">%s</span>', $this->avatar );
	}

	private function get_label(): string {
		return sprintf( '<span class="usp-bttn__text">%s</span>', $this->label );
	}

	private function get_counter(): string {
		return sprintf( '<span class="usp-bttn__count">%s</span>', $this->counter );
	}

	private function get_custom_content(): string {
		return $this->content;
	}

	public function get_button(): string {

		$this->setup_attrs();

		$content = sprintf( '<a %s>', $this->parse_attrs() );

		if ( $this->icon && 'left' == $this->icon_align ) {
			$content .= $this->get_icon();
		}

		if ( $this->avatar ) {
			$content .= $this->get_avatar();
		}

		if ( $this->label ) {
			$content .= $this->get_label();
		}

		if ( $this->icon && 'right' == $this->icon_align ) {
			$content .= $this->get_icon();
		}

		if ( $this->counter ) {
			$content .= $this->get_counter();
		}

		if ( $this->content ) {
			$content .= $this->get_custom_content();
		}

		$content .= '</a>';

		return $content;
	}

	public function add_class( string|array $class ): static {

		if ( is_array( $class ) ) {
			$this->class = array_merge( $this->class, $class );
		} else {
			$this->class[] = $class;
		}

		return $this;

	}

}
