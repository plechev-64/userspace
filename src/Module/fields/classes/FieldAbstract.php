<?php

class FieldAbstract {

	public ?string $id = null;
	public ?string $slug = null;
	public ?string $type = null;
	public ?string $icon = null;
	public ?string $title = null;
	public int|string|array|null $value = null;
	public int|string|array|null $default = null;
	public ?string $notice = null;
	public ?string $filter = null;
	public ?string $input_id = null;
	public ?string $input_name = null;
	public array $parent = [];
	public ?string $rand = null;
	public ?string $help = null;
	public ?string $class = null;
	public bool $required = false;
	public int|string $maxlength = 0;
	public array $children = [];
	public ?string $unique_id = null;
	public bool $value_in_key = false;
	public bool $must_delete = true;
	public bool $_new = false;

	public function __construct( array $args ) {

		if ( ! isset( $args['type'] ) ) {
			$args['type'] = 'custom';
		}

		if ( ! isset( $args['slug'] ) ) {
			if ( $args['type'] == 'custom' ) {
				$args['slug'] = md5( current_time( 'mysql' ) );
			} else {
				return;
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

	public function get_options(): array {
		return [];
	}

	private function init_properties( array $args ): void {

		foreach ( $args as $key => $val ) {
			$this->$key = $val;
		}

		if ( ! isset( $this->value ) && isset( $this->default ) ) {
			$this->value = $this->default;
		}
	}

	public function get_prop( string $propName ): mixed {
		return $this->isset_prop( $propName ) ? $this->$propName : false;
	}

	public function isset_prop( string $propName ): bool {
		return isset( $this->$propName );
	}

	public function set_prop( string $propName, mixed $value ): void {
		$this->$propName = $value;
	}

	public function get_title(): ?string {

		if ( ! $this->title ) {
			return null;
		}

		return '<div class="usp-field-title">'
		       . esc_html( $this->title ) . ( $this->required ? ' <span class="required">*</span>' : '' )
		       . '</div>';
	}

	public function get_icon(): ?string {

		if ( ! $this->icon ) {
			return null;
		}

		$content = '<span class="usp-field-icon">';
		$content .= '<i class="uspi ' . esc_attr( $this->icon ) . '" aria-hidden="true"></i> ';
		$content .= '</span>';

		return $content;
	}

	public function get_notice(): ?string {

		if ( ! $this->notice ) {
			return null;
		}

		return '<div class="usp-field-notice usps usps__ai-center">'
		       . '<i class="uspi fa-info-circle" aria-hidden="true"></i>'
		       . '<span>' . wp_kses_post( $this->notice ) . '</span>'
		       . '</div>';
	}

	public function is_new(): bool {
		return $this->_new;
	}

	public function get_field_input(): ?string {

		if ( ! $this->type ) {
			return null;
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

		return '<div id="usp-field-' . esc_attr( $this->id ) . '" class="' . esc_attr( implode( ' ', $classes ) ) . '">'
		           . '<div class="usp-field-core usps__relative">'
		           . $inputField
		           . '</div>'
		           . $this->get_notice()
		           . '</div>';

	}

	public function get_field_html( array $args = [] ): ?string {

		if ( $this->type == 'hidden' ) {
			return $this->get_field_input();
		}

		$classes = [ 'usp-field', 'type-' . $this->type . '-field' ];

		if ( isset( $args['classes'] ) ) {
			$classes = array_merge( $classes, $args['classes'] );
		}

		if ( $this->children ) {
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

	public function get_help(): ?string {

		if ( ! $this->help ) {
			return null;
		}

		return '<span class="usp-help-option" onclick="return usp_get_option_help(this);"><i class="uspi fa-question-circle" aria-hidden="true"></i><span class="help-content">' . esc_html( $this->help ) . '</span></span>';
	}

	public function get_children(): array {
		return $this->children;
	}

	public function isset_children(): bool {
		return (bool) $this->children;
	}

	protected function get_required(): string {
		return $this->required ? 'required="required"' : '';
	}

	protected function get_placeholder(): string {
		return $this->placeholder !== '' ? 'placeholder="' . esc_attr( $this->placeholder ) . '"' : '';
	}

	protected function get_maxlength(): string {
		return $this->maxlength ? 'maxlength="' . esc_attr( $this->maxlength ) . '"' : '';
	}

	protected function get_pattern(): string {
		return $this->pattern ? 'pattern="' . esc_attr( $this->pattern ) . '"' : '';
	}

	protected function get_min(): string {
		return $this->value_min !== '' ? 'min="' . esc_attr( $this->value_min ) . '"' : '';
	}

	protected function get_max(): string {
		return $this->value_max !== '' ? 'max="' . esc_attr( $this->value_max ) . '"' : '';
	}

	protected function get_input_id(): string {
		return $this->input_id ? 'id="' . esc_attr( $this->input_id ) . '"' : '';
	}

	public function get_class(): string {

		$class = [ $this->type . '-field' ];

		if ( $this->class ) {
			$class[] = $this->class;
		}

		return 'class="' . esc_attr( implode( ' ', $class ) ) . '"';
	}

	public function get_value(): int|null|string {

		if ( ! isset( $this->value ) || $this->value == '' ) {
			return null;
		}

		return $this->value;
	}

	public function get_field_value( string $title = null ): ?string {

		$value = $this->get_value();

		if ( ! is_numeric( $value ) && ( ! $value || ! $this->type ) ) {
			return null;
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

	public function get_filter_value(): string {
		return '<a href="' . $this->get_filter_url() . '">' . $this->get_value() . '</a>';
	}

	public function get_filter_url( $val = false ): ?string {

		if ( ! usp_get_option( 'usp_users_page' ) ) {
			return null;
		}

		if ( ! $val ) {
			$val = $this->value;
		}

		return add_query_arg( [ 'usergroup' => $this->slug . ':' . urlencode( $val ) ], get_permalink( usp_get_option( 'usp_users_page' ) ) );
	}

	/*
	 * Must be overridden in child class to validate passed value
	 */
	public function is_valid_value( $value ): bool {
		return true;
	}

	/*
	 * Must be overridden in child class to sanitize passed value
	 */
	public function sanitize_value( $value ): mixed {
		/**
		 * todo sanitize value in all fields
		 */
		return $value;
	}

}
