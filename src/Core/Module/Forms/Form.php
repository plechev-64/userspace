<?php

namespace USP\Core\Module\Forms;

use USP\Core\Module\Fields\Fields;

class Form extends Fields {

	public ?string $class = null;
	public ?string $action = null;
	public ?string $method = 'post';
	public ?string $icon = 'fa-check-circle';
	public ?string $target = null;
	public ?string $submit = null;
	public array $submit_args = [];
	public ?string $nonce_name = null;
	public ?string $onclick = null;
	public array $values = [];

	public function __construct( array $args = [] ) {

		$this->init_properties( $args );

		$this->fields = [];

		parent::__construct( $args['fields'], $args['structure'] ?? false );
	}

	private function init_properties( array $args ): void {

		$properties = get_class_vars( get_class( $this ) );

		foreach ( $properties as $name => $val ) {
			if ( isset( $args[ $name ] ) ) {
				$this->$name = $args[ $name ];
			}
		}
	}

	public function get_form( array $args = [] ): string {

		$content = '<div class="' . ( $this->class ? $this->class . ' ' : '' ) . 'usp-form usp-preloader-parent">';

		$content .= '<form method="' . $this->method . '" action="' . $this->action . '" target="' . $this->target . '">';

		$content .= $this->get_fields_list();

		$content .= $this->get_submit_box();

		if ( $this->nonce_name ) {
			$content .= wp_nonce_field( $this->nonce_name, '_wpnonce', true, false );
		}

		$content .= '</form>';

		$content .= '</div>';

		return $content;
	}

	public function get_submit_box(): string {

		$content = '<div class="submit-box usps usps__jc-end">';

		if ( $this->onclick ) {
			$content .= usp_get_button( wp_parse_args( $this->submit_args, [
				'label'     => $this->submit,
				'icon'      => $this->icon,
				'onclick'   => $this->onclick,
				'fullwidth' => '1',
				'size'      => 'medium'
			] ) );
		} else {
			$content .= usp_get_button( wp_parse_args( $this->submit_args, [
				'label'     => $this->submit,
				'icon'      => $this->icon,
				'submit'    => true,
				'fullwidth' => '1',
				'size'      => 'medium'
			] ) );
		}

		$content .= '</div>';

		return $content;
	}

	public function get_fields_list(): ?string {

		if ( ! $this->fields ) {
			return null;
		}

		$content = '';

		if ( $this->structure ) {
			$content .= $this->get_content_form();
		} else {
			foreach ( $this->fields as $field_id => $field ) {
				$content .= $this->get_form_field( $field_id );
			}
		}

		return $content;
	}

	private function get_form_field( $field_id ): ?string {

		$field = $this->get_field( $field_id );

		if ( ! $field ) {
			return null;
		}

		if ( ! isset( $field->value ) ) {
			$field->value = ( isset( $this->values[ $field->slug ] ) ) ? $this->values[ $field->slug ] : null;
		}

		return $field->get_field_html();
	}

}
