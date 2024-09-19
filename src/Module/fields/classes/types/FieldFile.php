<?php

class FieldFile extends FieldUploader {

	public ?string $mode_output = 'list';
	public bool $multiple = false;

	public function __construct( array $args ) {

		if ( isset( $args['ext-files'] ) ) {
			$args['file_types'] = $args['ext-files'];
		}

		if ( isset( $args['sizefile'] ) ) {
			$args['max_size'] = $args['sizefile'];
		}

		parent::__construct( $args );
	}

	public function get_options(): array {
		return [
			[
				'slug'       => 'max_size',
				'default'    => $this->max_size,
				'type'       => 'runner',
				'unit'       => 'Kb',
				'value_min'  => 256,
				'value_max'  => 5120,
				'value_step' => 256,
				'title'      => __( 'File size', 'userspace' ),
				'notice'     => __( 'Maximum size of uploaded file, Kb (Default - 512)', 'userspace' )
			],
			[
				'slug'    => 'file_types',
				'default' => $this->file_types,
				'type'    => 'text',
				'title'   => __( 'Allowed file types', 'userspace' ),
				'notice'  => __( 'Allowed types of files are divided by comma, for example: pdf, zip, jpg', 'userspace' )
			]
		];
	}

	public function get_uploader_props(): array {

		return wp_parse_args( $this->uploader_props, [
			'user_id'      => get_current_user_id(),
			'multiple'     => 0,
			'temp_media'   => 1,
			'max_size'     => $this->max_size,
			'auto_upload'  => 1,
			'file_types'   => array_map( 'trim', explode( ',', $this->file_types ) ),
			'max_files'    => 1,
			'crop'         => $this->crop,
			'filename'     => $this->filename,
			'filetitle'    => $this->filetitle,
			'input_attach' => $this->input_name,
			'mode_output'  => 'list'
		] );
	}

}
