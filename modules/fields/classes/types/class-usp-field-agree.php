<?php

class USP_Field_Agree extends USP_Field_Abstract {

	public $text_confirm;
	public $url_agreement;

	function __construct( $args ) {

		if ( isset( $args['text-confirm'] ) ) {
			$args['text_confirm'] = $args['text-confirm'];
		}

		if ( isset( $args['url-agreement'] ) ) {
			$args['url_agreement'] = $args['url-agreement'];
		}

		parent::__construct( $args );
	}

	function get_options() {

		return array(
			array(
				'slug'        => 'icon',
				'default'     => 'fa-check-square',
				'placeholder' => 'fa-check-square',
				'class'       => 'usp-iconpicker',
				'type'        => 'text',
				'title'       => __( 'Icon class of usp-awesome', 'userspace' )
			),
			array(
				'slug'    => 'url_agreement',
				'default' => $this->url_agreement,
				'type'    => 'url',
				'title'   => __( 'Agreement URL', 'userspace' )
			),
			array(
				'slug'    => 'text_confirm',
				'default' => $this->text_confirm,
				'type'    => 'textarea',
				'title'   => __( 'Consent confirmation text', 'userspace' )
			)
		);
	}

	function get_title() {

		if ( ! $this->title ) {
			$this->title = __( 'Agreement', 'userspace' );
		}

		if ( $this->url_agreement ) {
			return '<a href="' . $this->url_agreement . '" class="usp-agree usps__inline" target="_blank">' . $this->title . ( $this->required ? ' <span class="required">*</span>' : '' ) . '</a>';
		}

		return $this->title . ( $this->required ? ' <span class="required">*</span>' : '' );
	}

	function get_value() {

		if ( $this->value ) {
			return __( 'Accepted', 'userspace' );
		}

		return false;
	}

	function get_filter_value() {
		return '<a href="' . $this->get_filter_url() . '" target="_blank">' . $this->value . '</a>';
	}

	function get_input() {

		$text = $this->text_confirm ?: __( 'I agree with the text of the agreement', 'userspace' );

		$input = '<span class="usp-checkbox-box usps__inline usps__relative">';
		$input .= '<input type="checkbox" ' . checked( $this->value, 1, false ) . ' ' . $this->get_required() . ' name="' . $this->input_name . '" id="' . $this->input_id . $this->rand . '" value="1"/> ';
		$input .= '<label class="usp-label usps usps__ai-center usps__no-select" for="' . $this->input_id . $this->rand . '">' . $text . '</label>';
		$input .= '</span>';

		return $input;
	}

	function is_valid_value( $value ) {
		return (int) $value === 1;
	}

}
