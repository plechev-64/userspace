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

		return [
			[
				'slug'   => 'tinymce',
				'type'   => 'radio',
				'title'  => __( 'TinyMCE', 'userspace' ),
				'values' => [
					__( 'Disabled', 'userspace' ),
					__( 'Using TinyMCE', 'userspace' )
				],
				'notice' => __( 'May not load with AJAX', 'userspace' )
			],
			[
				'slug'   => 'media_button',
				'type'   => 'radio',
				'title'  => __( 'Media uploader WordPress', 'userspace' ),
				'values' => [
					__( 'Disabled', 'userspace' ),
					__( 'Enabled', 'userspace' )
				]
			]
		];
	}

	function get_input() {

		$editor_id = $this->editor_id ?: 'editor-' . $this->rand;

		$data = [
			'wpautop'       => 1,
			'media_buttons' => $this->media_button,
			'textarea_name' => $this->input_name,
			'textarea_rows' => 10,
			'tabindex'      => null,
			'editor_css'    => '',
			'editor_class'  => 'autosave',
			'teeny'         => 0,
			'dfw'           => 0,
			'tinymce'       => (bool) $this->tinymce,
			'quicktags'     => $this->quicktags ? [ 'buttons' => $this->quicktags ] : true
		];

		ob_start();

		wp_editor( $this->value, $editor_id, $data );

		if ( usp_is_ajax() ) {
			global $wp_scripts, $wp_styles;

			$wp_scripts->do_items( [ 'quicktags' ] );
			$wp_styles->do_items( [ 'buttons' ] );
		}

		$content = ob_get_contents();

		if ( usp_is_ajax() ) {
			$content .= '<script>usp_init_ajax_editor("' . esc_js( $editor_id ) . '",' . json_encode( [
					'tinymce'    => $this->tinymce,
					'qt_buttons' => $this->quicktags ?: false
				] ) . ');</script>';
		}

		ob_end_clean();

		return $content;
	}

	function get_value() {

		if ( ! $this->value ) {
			return false;
		}

		return wp_kses_post( nl2br( $this->value ) );
	}

}
