<?php

class USP_Field_Editor extends USP_Field_Abstract {

	public $tinymce;
	public $html_editor = 1;
	public $editor_id;
	public $quicktags;
	public $media_button;

	function __construct( $args ) {

		if ( isset( $args['editor-id'] ) ) {
			$args['editor_id'] = $args['editor-id'];
		}

		parent::__construct( $args );
	}

	function get_options() {

		return array(
			array(
				'slug'   => 'tinymce',
				'type'   => 'radio',
				'title'  => __( 'TinyMCE', 'userspace' ),
				'values' => array(
					__( 'Disabled', 'userspace' ),
					__( 'Using TinyMCE', 'userspace' )
				),
				'notice' => __( 'May not load with AJAX', 'userspace' )
			),
			array(
				'slug'   => 'media_button',
				'type'   => 'radio',
				'title'  => __( 'Media uploader WordPress', 'userspace' ),
				'values' => array(
					__( 'Disabled', 'userspace' ),
					__( 'Enabled', 'userspace' )
				)
			)
		);
	}

	function get_input() {

		$editor_id = $this->editor_id ? $this->editor_id : 'editor-' . $this->rand;

		$data = array(
			'wpautop'       => 1
		,
			'media_buttons' => $this->media_button
		,
			'textarea_name' => $this->input_name
		,
			'textarea_rows' => 10
		,
			'tabindex'      => null
		,
			'editor_css'    => ''
		,
			'editor_class'  => 'autosave'
		,
			'teeny'         => 0
		,
			'dfw'           => 0
		,
			'tinymce'       => $this->tinymce ? true : false
		,
			'quicktags'     => $this->quicktags ? array( 'buttons' => $this->quicktags ) : true
		);

		ob_start();

		wp_editor( $this->value, $editor_id, $data );

		if ( usp_is_ajax() ) {
			global $wp_scripts, $wp_styles;

			$wp_scripts->do_items( array(
				'quicktags'
			) );

			$wp_styles->do_items( array(
				'buttons'
			) );
		}

		$content = ob_get_contents();

		if ( usp_is_ajax() ) {
			$content .= '<script>usp_init_ajax_editor("' . $editor_id . '",' . json_encode( array(
					'tinymce'    => $this->tinymce,
					'qt_buttons' => $this->quicktags ? $this->quicktags : false
				) ) . ');</script>';
		}

		ob_end_clean();

		return $content;
	}

	function get_value() {

		if ( ! $this->value ) {
			return false;
		}

		return nl2br( $this->value );
	}

}
