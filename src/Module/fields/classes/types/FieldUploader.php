<?php

class FieldUploader extends FieldAbstract {

	public ?string $fix_editor = null;
	public ?string $filename = null;
	public ?string $filetitle = null;
	public ?string $file_types = 'jpg, jpeg, png';
	public int $crop = 0;
	public int $max_size = 512;
	public int $max_files = 5;
	public bool $multiple = true;
	public bool $dropzone = false;
	public ?string $mode_output = 'grid';
	public bool $temp_media = true;
	public ?string $image_thumb = 'thumbnail';
	public array $uploader_props = array();

	public function __construct( array $args ) {
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
			],
			[
				'slug'       => 'max_files',
				'default'    => $this->max_files,
				'type'       => 'runner',
				'value_min'  => 1,
				'value_max'  => 100,
				'value_step' => 1,
				'title'      => __( 'Max number of files', 'userspace' ),
			],
			[
				'slug'    => 'dropzone',
				'default' => $this->dropzone,
				'type'    => 'radio',
				'values'  => [
					__( 'Disabled', 'userspace' ),
					__( 'Enabled', 'userspace' )
				],
				'title'   => __( 'Dropzone', 'userspace' ),
			],
			[
				'slug'    => 'mode_output',
				'default' => $this->mode_output,
				'type'    => 'radio',
				'values'  => [
					'grid' => __( 'Cards', 'userspace' ),
					'list' => __( 'List', 'userspace' ),
					//'gallery'	 => __( 'Gallery', 'userspace' )
				],
				'title'   => __( 'Mode of files output', 'userspace' ),
			],
			[
				'slug'    => 'fix_editor',
				'default' => $this->fix_editor,
				'type'    => 'text',
				'title'   => __( 'ID of an attaching editor', 'userspace' ),
				'notice'  => __( 'You can attach this uploader for one of text editors, pointing its ID', 'userspace' ),
			]
		];

	}

	public function get_uploader_props(): array {
		global $user_ID;

		return wp_parse_args( $this->uploader_props, array(
			'temp_media'   => $this->temp_media,
			'fix_editor'   => $this->fix_editor,
			'required'     => intval( $this->required ),
			'user_id'      => $user_ID,
			'min_width'    => 200,
			'min_height'   => 200,
			'filename'     => $this->filename,
			'filetitle'    => $this->filetitle,
			'dropzone'     => $this->dropzone,
			'multiple'     => $this->multiple,
			'max_size'     => $this->max_size,
			'auto_upload'  => $this->multiple ? 1 : 0,
			'file_types'   => array_map( 'trim', explode( ',', $this->file_types ) ),
			'max_files'    => $this->max_files,
			'crop'         => $this->multiple ? 0 : $this->crop,
			'input_attach' => $this->input_name,
			'mode_output'  => $this->mode_output,
			'image_thumb'  => $this->image_thumb
		) );
	}

	public function get_uploader(): Uploader {
		return new Uploader( $this->id, $this->get_uploader_props() );
	}

	public function get_input(): string {

		$uploader = $this->get_uploader();

		$content = '';

		if ( usp_is_ajax() ) {

			ob_start();

			/* global $wp_scripts, $wp_styles;

			  $wp_scripts->do_items( array(
			  //'jquery-ui-core',
			  'fileupload-ui-widget',
			  //'jquery-ui-widget',
			  'jquery-ui-sortable',
			  'usp-core-scripts',
			  'jquery-iframe-transport',
			  'jquery-fileupload',
			  'jquery-fileupload-process',
			  'jquery-fileupload-image',
			  'usp-uploader-scripts',
			  ) );

			  $wp_styles->do_items( array(
			  'usp-uploader-style'
			  ) );

			  $content .= ob_get_contents(); */

			ob_end_clean();
		}

		$content .= $uploader->get_gallery( $this->value, true );

		$content .= $uploader->get_uploader();

		return $content;
	}

	public function get_value(): ?string {

		if ( ! $this->value ) {
			return null;
		}

		$attachList = '';

		if ( $this->mode_output == 'gallery' ) {

			/* $width = 100;

			  $galArgs = array(
			  'id' => 'usp-gallery-'.$this->id,
			  'attach_ids' => $this->value,
			  //'center_align' => true,
			  //'width' => (count($this->value) < 7)? count($this->value) * 73: 500,
			  'height' => $width,
			  'slides' => array(
			  'slide' => array($width,$width),
			  'full' => 'large'
			  ),
			  'options' => array(
			  '$SlideWidth' => $width,
			  '$SlideSpacing' => 3
			  )
			  );

			  if(count($attach_ids) >= 7){
			  $galArgs['navigator'] = array(
			  'arrows' => true
			  );
			  }

			  $content = usp_get_image_gallery($galArgs); */

			$content = usp_get_image_gallery( array(
				'id'           => 'usp-gallery-' . $this->id,
				'center_align' => true,
				'attach_ids'   => $this->value,
				//'width' => 500,
				'height'       => 250,
				'slides'       => array(
					'slide' => 'large',
					'full'  => 'large'
				),
				'navigator'    => array(
					'thumbnails' => array(
						'width'  => 50,
						'height' => 50,
						'arrows' => true
					)
				)
			) );
		} else {

			$attachIDs = is_array( $this->value ) ? $this->value : array( $this->value );

			global $wpdb;
			$IDs = ( new QueryBuilder( [
				'name' => $wpdb->posts,
				'cols' => [ 'ID', 'post_type' ]
			] ) )->select( [ 'ID' ] )
			     ->where( [ 'post_type' => 'attachment', 'ID__in' => $attachIDs ] )
			     ->limit( - 1 )
			     ->orderby( 'post_title', 'ASC' )
			     ->get_col();

			foreach ( $IDs as $ID ) {
				$attachList .= $this->get_single_attachment( $ID );
			}
		}

		if ( ! $attachList ) {
			return null;
		}

		$content = '<div id="usp-gallery-' . esc_attr( $this->id ) . '" class="usp-media usp-media-' . esc_attr( $this->mode_output ) . ' usps__relative">';
		$content .= $attachList;
		$content .= '</div>';

		return $content;
	}

	public function get_single_attachment( $attach_id ): ?string {

		$is_image = wp_attachment_is_image( $attach_id ) ? true : false;

		if ( $is_image ) {

			$image = wp_get_attachment_image( $attach_id, 'thumbnail', false, [ 'class' => 'usps__img-reset' ] );
		} else {

			$image = wp_get_attachment_image( $attach_id, [ 100, 100 ], true );
		}

		if ( ! $image ) {
			return null;
		}

		$url = wp_get_attachment_url( $attach_id );

		$content = '<div class="usp-media__item usp-media__item-' . esc_attr( $attach_id ) . ' ' . ( $is_image ? 'type-image' : 'type-file' ) . ' usps__inline usps__relative">';

		$content .= '<a href="' . esc_url( $url ) . '" target="_blank">' . $image . '</a>';

		$content .= '<div class="usp-file-name">';
		$content .= '<a href="' . esc_url( $url ) . '" target="_blank">' . esc_html( basename( get_post_field( 'guid', $attach_id ) ) ) . '</a>';
		$content .= '</div>';

		$content .= '</div>';

		return $content;
	}

}
