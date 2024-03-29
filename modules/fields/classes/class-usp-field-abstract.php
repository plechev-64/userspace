<?php

class USP_Field_Abstract {

	public $id;
	public $slug;
	public $type;
	public $icon;
	public $title;
	public $value = null;
	public $default = null;
	public $notice;
	public $filter;
	public $input_id;
	public $input_name;
	public $parent;
	public $rand;
	public $help;
	public $class;
	public $required;
	public $maxlength;
	public $childrens;
	public $unique_id = false;
	public $value_in_key = null;
	public $must_delete = true;
	public $_new;

	function __construct( $args ) {

		if ( ! isset( $args['type'] ) ) {
			$args['type'] = 'custom';
		}

		if ( ! isset( $args['slug'] ) ) {
			if ( $args['type'] == 'custom' ) {
				$args['slug'] = md5( current_time( 'mysql' ) );
			} else {
				return false;
			}
		}

		if ( isset( $args['name'] ) ) {
			$args['input_name'] = $args['name'];
		}

		$this->id = $args['slug'];

		$this->init_properties( $args );

		$this->rand = uniqid();

		if ( ! $this->input_name ) {
			$this->input_name = $this->id;
		}

		if ( ! $this->input_id ) {
			$this->input_id = $this->id;
		}

		if ( $this->unique_id ) {
			$this->input_id .= $this->rand;
		}
	}

	function get_options() {
		return [];
	}

	function init_properties( $args ) {

		foreach ( $args as $key => $val ) {
			$this->$key = $val;
		}

		if ( ! isset( $this->value ) && isset( $this->default ) ) {
			$this->value = $this->default;
		}
	}

	function get_prop( $propName ) {
		return $this->isset_prop( $propName ) ? $this->$propName : false;
	}

	function isset_prop( $propName ) {
		return isset( $this->$propName );
	}

	function set_prop( $propName, $value ) {
		$this->$propName = $value;
	}

	function get_title() {

		if ( ! $this->title ) {
			return false;
		}

		return '<div class="usp-field-title">'
		       . esc_html( $this->title ) . ( $this->required ? ' <span class="required">*</span>' : '' )
		       . '</div>';
	}

	function get_icon() {

		if ( ! $this->icon ) {
			return false;
		}

		$content = '<span class="usp-field-icon">';
		$content .= '<i class="uspi ' . esc_attr( $this->icon ) . '" aria-hidden="true"></i> ';
		$content .= '</span>';

		return $content;
	}

	function get_notice() {

		if ( ! $this->notice ) {
			return false;
		}

		return '<div class="usp-field-notice usps usps__ai-center">'
		       . '<i class="uspi fa-info-circle" aria-hidden="true"></i>'
		       . '<span>' . wp_kses_post( $this->notice ) . '</span>'
		       . '</div>';
	}

	function is_new() {
		return $this->_new;
	}

	function get_field_input() {

		if ( ! $this->type ) {
			return false;
		}

		$classes = [ 'type-' . $this->type . '-input' ];

		$classes[] = 'usp-field-input';

		if ( isset( $this->get_value ) && $this->get_value ) {
			$inputField = $this->get_field_value();
		} else {
			$inputField = $this->get_input();
		}

		if ( ! $this->title && $this->required ) {
			$inputField .= '<span class="required">*</span>';
		}

		if ( $this->maxlength ) {
			$inputField .= '<script>usp_init_field_maxlength("' . esc_js( $this->input_id ) . '");</script>';
		}

		$content = '<div id="usp-field-' . esc_attr( $this->id ) . '" class="' . esc_attr( implode( ' ', $classes ) ) . '">'
		           . '<div class="usp-field-core usps__relative">'
		           . $inputField
		           . '</div>'
		           . $this->get_notice()
		           . '</div>';

		return $content;
	}

	function get_field_html( $args = false ) {

		if ( $this->type == 'hidden' ) {
			return $this->get_field_input();
		}

		$classes = [ 'usp-field', 'type-' . $this->type . '-field' ];

		if ( isset( $args['classes'] ) ) {
			$classes = array_merge( $classes, $args['classes'] );
		}

		if ( $this->childrens ) {
			$classes[] = 'usp-parent-field';
		}

		if ( $this->parent ) {
			$classes[] = 'usp-children-field';
		}

		$content = '<div id="usp-field-' . esc_attr( $this->id ) . '-wrapper" class="' . esc_attr( implode( ' ', $classes ) ) . '" ' . ( $this->parent ? 'data-parent="' . esc_attr( $this->parent['id'] ) . '" data-parent-value="' . esc_attr( $this->parent['value'] ) . '"' : '' ) . '>';

		$content .= $this->get_title();

		$content .= $this->get_help();

		$content .= $this->get_field_input();

		$content .= '</div>';

		return $content;
	}

	function get_help() {

		if ( ! $this->help ) {
			return '';
		}

		return '<span class="usp-help-option" onclick="return usp_get_option_help(this);"><i class="uspi fa-question-circle" aria-hidden="true"></i><span class="help-content">' . esc_html( $this->help ) . '</span></span>';
	}

	function get_childrens() {
		return $this->childrens;
	}

	function isset_childrens() {
		return (bool) $this->childrens;
	}

	protected function get_required() {
		return $this->required ? 'required="required"' : '';
	}

	protected function get_placeholder() {
		return $this->placeholder !== '' ? 'placeholder="' . esc_attr( $this->placeholder ) . '"' : '';
	}

	protected function get_maxlength() {
		return $this->maxlength ? 'maxlength="' . esc_attr( $this->maxlength ) . '"' : '';
	}

	protected function get_pattern() {
		return $this->pattern ? 'pattern="' . esc_attr( $this->pattern ) . '"' : '';
	}

	protected function get_min() {
		return $this->value_min !== '' ? 'min="' . esc_attr( $this->value_min ) . '"' : '';
	}

	protected function get_max() {
		return $this->value_max !== '' ? 'max="' . esc_attr( $this->value_max ) . '"' : '';
	}

	protected function get_input_id() {
		return $this->input_id ? 'id="' . esc_attr( $this->input_id ) . '"' : '';
	}

	function get_class() {

		$class = [ $this->type . '-field' ];

		if ( $this->class ) {
			$class[] = $this->class;
		}

		return 'class="' . esc_attr( implode( ' ', $class ) ) . '"';
	}

	function get_value() {

		if ( ! isset( $this->value ) || $this->value == '' ) {
			return false;
		}

		return $this->value;
	}

	function get_field_value( $title = false ) {

		$value = $this->get_value();

		if ( ! is_numeric( $value ) && ( ! $value || ! $this->type ) ) {
			return false;
		}

		$content = '<div class="usp-field usps type-' . esc_attr( $this->type ) . '-value usp-field-' . esc_attr( $this->id ) . '">';

		//$content .= $this->get_icon();

		if ( $title ) {
			$content .= '<div class="usp-field-title-box usps usps__nowrap"><div class="usp-field-title">'
			            . esc_html( $this->title )
			            . '</div>'
			            . '<span class="title-colon">: </span></div>';
		}

		$content .= '<span class="usp-field-value usps__ml-6">';

		$content .= $this->filter ? $this->get_filter_value() : $value;

		$content .= '</span>';

		$content .= '</div>';

		return $content;
	}

	function get_filter_value() {
		return '<a href="' . $this->get_filter_url() . '">' . $this->get_value() . '</a>';
	}

	function get_filter_url( $val = false ) {

		if ( ! usp_get_option( 'usp_users_page' ) ) {
			return false;
		}

		if ( ! $val ) {
			$val = $this->value;
		}

		return add_query_arg( [ 'usergroup' => $this->slug . ':' . urlencode( $val ) ], get_permalink( usp_get_option( 'usp_users_page' ) ) );
	}

	/*
	 * Must be overridden in child class to validate passed value
	 */
	function is_valid_value( $value ) {
		return true;
	}

	/*
	 * Must be overridden in child class to sanitize passed value
	 */
	function sanitize_value( $value ) {
		/**
		 * todo sanitize value in all fields
		 */
		return $value;
	}

}
