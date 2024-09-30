<?php

namespace USP\Core\Module\Fields\Type;

use USP\Core\Module\Fields\FieldAbstract;

class FieldEditor extends FieldAbstract {

	public bool $tinymce = false;
	public bool $html_editor = true;
	public ?string $editor_id = null;
	public bool $quicktags = false;
	public bool $media_button = false;

	public function get_options(): array {

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

	public function get_input(): string {

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
			'tinymce'       => $this->tinymce,
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

	public function get_value(): ?string {

		if ( ! $this->value ) {
			return null;
		}

		return wp_kses_post( nl2br( $this->value ) );
	}

}
