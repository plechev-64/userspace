<?php

class USP_Button {

	public $id;
	public $onclick;
	public $href = 'javascript:void(0);';
	public $class = [];
	public $type = 'primary'; // clear, simple, primary
	public $style;
	public $icon; // for example: fa-car
	public $icon_align = 'left'; // left or right position
	public $icon_mask;  // 1 - is mask on icon
	public $label;   // text on button
	public $title;   // title attribute
	public $counter; // number
	public $content;
	public $avatar;  // avatar button
	public $avatar_circle; // round avatar
	public $data;
	public $submit;
	public $status;  // state of the button: loading, disabled, active
	public $size = 'standard';   // small, standard, medium, large, big
	public $attr; // deprecated
	public $attrs;
	public $fullwidth;  // 1 - is fullwidth button
	public $inset;

	function __construct( $args ) {

		if ( ! isset( $args['title'] ) && isset( $args['label'] ) ) {
			$args['title'] = $args['label'];
		}

		$this->init_properties( $args );

		$this->setup_class();

	}

	function init_properties( $args ) {

		$properties = get_class_vars( get_class( $this ) );

		foreach ( $properties as $name => $val ) {
			if ( isset( $args[ $name ] ) & ! empty( $args[ $name ] ) ) {
				$this->$name = $args[ $name ];
			}
		}
	}

	function setup_attrs() {
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

	function setup_class() {

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

	function parse_attrs() {

		$attrs = [];
		foreach ( $this->attrs as $name => $value ) {
			if ( ! $value ) {
				continue;
			}
			$attrs[] = $name . '=\'' . $value . '\'';
		}

		if ( $this->attr ) // deprecated
		{
			$attrs[] = $this->attr;
		}

		return implode( ' ', $attrs );
	}

	function get_icon() {
		return sprintf( '<i class="usp-bttn__ico usp-bttn__ico-%1$s uspi %2$s"></i>', $this->icon_align, $this->icon );
	}

	function get_avatar() {
		return sprintf( '<span class="usp-bttn__ava">%s</span>', $this->avatar );
	}

	function get_label() {
		return sprintf( '<span class="usp-bttn__text">%s</span>', $this->label );
	}

	function get_counter() {
		return sprintf( '<span class="usp-bttn__count">%s</span>', $this->counter );
	}

	function get_custom_content() {
		return $this->content;
	}

	function get_button() {

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

	function add_class( $class ) {

		if ( is_array( $class ) ) {
			$this->class = array_merge( $this->class, $class );
		} else {
			$this->class[] = $class;
		}

		return $this;

	}

}
