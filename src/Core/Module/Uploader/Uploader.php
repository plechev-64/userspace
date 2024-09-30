<?php

namespace USP\Core\Module\Uploader;

use USP\Core\Query\DefaultTable\TempMediaQuery;

class Uploader {

	public ?string $uploader_id = null;
	public ?bool $fix_editor = false;
	public string $action = 'usp_upload';
	public bool $temp_media = false;
	public bool $input_attach = false;
	public bool $auto_upload = true;
	public int $user_id = 0;
	public int $post_parent = 0;
	public string $input_name = 'usp-upload';
	public bool $dropzone = false;
	public int $max_files = 10;
	public int $max_size = 512;
	public ?int $min_width = null;
	public ?int $min_height = null;
	public array|string $resize = array();
	public array|string $file_types = array( 'jpg', 'png', 'jpeg' );
	public bool $multiple = false;
	public array|int $crop = [];
	public array $image_sizes = [];
	public string $mode_output = 'grid';
	public bool $manager_balloon = false;
	public ?string $class_name = '';
	public ?string $filename = '';
	public ?string $filetitle = '';
	public ?string $dir = '';
	public string $image_thumb = 'thumbnail';
	protected array $accept = array( 'image/*' );

	public function __construct( string $uploader_id, array $args = [] ) {

		if ( ! isset( $args['user_id'] ) ) {
			global $user_ID;
			$args['user_id'] = $user_ID;
		}

		$args['class_name'] = get_class( $this );

		$this->uploader_id = $uploader_id;

		if ( $args ) {
			$this->init_properties( $args );
		}

		if ( ! is_array( $this->file_types ) ) {
			$this->file_types = array_map( 'trim', explode( ',', $this->file_types ) );
		}

		if ( ! $this->file_types ) {
			$this->file_types = array( 'jpg', 'png', 'jpeg' );
		}

		if ( $this->resize && ! is_array( $this->resize ) ) {
			$this->resize = array_map( 'trim', explode( ',', $this->resize ) );
		}

		$this->accept = $this->get_accept();

		$this->init_scripts();
	}

	protected function init_scripts(): void {

		usp_fileupload_scripts();
		usp_dialog_scripts();
		usp_crop_scripts();

		if ( $this->crop ) {
			//usp_dialog_scripts();
			//usp_crop_scripts();
		}
	}

	private function init_properties( array $args ): void {

		$properties = get_class_vars( get_class( $this ) );

		foreach ( $args as $name => $value ) {
			//if(isset($args[$name])){
			//$value = $args[$name];

			if ( is_array( $value ) ) {

				foreach ( $value as $k => $v ) {
					if ( is_object( $v ) ) {
						$value[ $k ] = ( array ) $v;
					}
				}
				$value = ( array ) $value;
			} else if ( is_object( $value ) ) {
				$value = ( array ) $value;
			}

			$this->$name = $value;
			//}
		}
	}

	protected function filter_attachment_manager_items( array $items, int $attach_id = null ): array {
		return $items;
	}

	protected function after_upload( array $uploads ): void {
		return;
	}

	protected function get_attachment_title( int $attach_id ): string {
		return basename( get_post_field( 'guid', $attach_id ) );
	}

	protected function get_progress_bar(): string {
		return '<div class="usp-uploader-progress"></div>';
	}

	public function get_uploader( array $args = [] ): string {

		$defaults = array(
			'allowed_types' => true
		);

		$args = wp_parse_args( $args, $defaults );

		$content = '<div id="usp-uploader-' . $this->uploader_id . '" class="usp-uploader">';

		if ( $this->dropzone ) {
			$content .= $this->get_dropzone();
		}

		$content .= '<div class="usp-media__uploader">';

		$content .= $this->get_progress_bar();

		$content .= $this->get_button( $args );

		if ( $args['allowed_types'] ) {
			$content .= '<small class="usp-type-info">' . __( 'Types of files', 'userspace' ) . ': ' . implode( ', ', $this->file_types ) . '</small>';
		}

		$content .= '</div>';

		$content .= '</div>';

		return $content;
	}

	public function get_input(): string {

		$json = wp_json_encode( $this );

		$content = '<input id="usp-uploader-input-' . $this->uploader_id . '" class="usp-uploader-input" '
		           . 'data-uploader_id="' . $this->uploader_id . '" name="' . ( $this->multiple ? $this->input_name . '[]' : $this->input_name ) . '" '
		           . 'type="file" accept="' . implode( ', ', $this->accept ) . '" ' . ( $this->multiple ? 'multiple' : '' ) . '>'
		           . '<script>usp_init_uploader(' . $json . ', "' . md5( $json . usp_get_option( 'usp_security_key' ) ) . '");</script>';

		if ( usp_is_ajax() ) {
			$content .= '<script>USPUploaders.init();</script>';
		}

		return $content;
	}

	public function get_button( array $args ): string {

		$defaults = array(
			'button_label' => __( 'Upload file', 'userspace' ),
			'button_icon'  => 'fa-upload',
			'button_type'  => 'simple'
		);

		$args = wp_parse_args( $args, $defaults );

		$bttnArgs = array(
			'icon'    => $args['button_icon'],
			'type'    => $args['button_type'],
			'label'   => $args['button_label'],
			'class'   => [ 'usp-uploader-bttn', 'usp-uploader-bttn-' . $this->uploader_id ],
			'content' => $this->get_input()
		);

		return usp_get_button( $bttnArgs );
	}

	public function get_dropzone(): string {

		return '<div id="usp-dropzone-' . esc_attr( $this->uploader_id ) . '" class="usp-dropzone">
				<div class="dropzone-upload-area">
					' . __( 'Add files in a queue of downloads', 'userspace' ) . '
				</div>
			</div>';

	}

	private function get_mime_type_by_ext( string $file_ext ): ?string {

		if ( ! $file_ext ) {
			return null;
		}

		$mimes = get_allowed_mime_types();

		foreach ( $mimes as $type => $mime ) {
			if ( str_contains( $type, $file_ext ) ) {
				return $mime;
			}
		}

		return null;
	}

	private function get_accept(): ?array {

		if ( ! $this->file_types ) {
			return null;
		}

		$accept = array();

		foreach ( $this->file_types as $type ) {
			if ( ! $type ) {
				continue;
			}
			$accept[] = $this->get_mime_type_by_ext( $type );
		}

		return $accept;
	}

	public function get_gallery( array|int $imagIds = [], $getTemps = false ): string {

		if ( ! $imagIds && $getTemps ) {
			$session_id = ! empty( $_COOKIE['PHPSESSID'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['PHPSESSID'] ) ) : 'none';

			$imagIds = ( new TempMediaQuery() )->select( [ 'media_id' ] )
			                                   ->where( [
				                                   'uploader_id' => $this->uploader_id,
				                                   'user_id'     => $this->user_id ?: 0,
				                                   'session_id'  => $this->user_id ? '' : $session_id,
			                                   ] )
			                                   ->get_col();
		}

		$content = '<div id="usp-media-' . $this->uploader_id . '" class="usp-media usp-media-' . $this->mode_output . ' ' . ( $this->manager_balloon ? 'balloon-manager' : 'simple-manager' ) . ' usps__relative">';

		if ( $imagIds ) {
			//$content .= '<div class="ui-sortable-placeholder"></div>';
			if ( is_array( $imagIds ) ) {
				foreach ( $imagIds as $imagId ) {
					$content .= $this->gallery_attachment( $imagId );
				}
			} else {
				$content .= $this->gallery_attachment( $imagIds );
			}
		}

		$content .= '</div>';

		return $content;
	}

	protected function gallery_attachment( int $attach_id ): ?string {

		$attach = get_post( $attach_id );

		if ( ! $attach ) {
			return null;
		}

		$is_image = wp_attachment_is( 'image', $attach );

		if ( $is_image ) {

			$image = wp_get_attachment_image( $attach_id, $this->image_thumb, false, [ 'class' => 'usps__img-reset' ] );
		} else {

			$image = wp_get_attachment_image( $attach_id, array( 100, 100 ), true );
		}

		if ( ! $image ) {
			return null;
		}

		$content = '<div class="usp-media__item usp-media__item-' . $attach_id . ' ' . ( $is_image ? 'type-image' : 'type-file' ) . ' usps__inline usps__relative" id="gallery-' . $this->uploader_id . '-attachment-' . $attach_id . '">';

		$content .= $image;

		$content .= '<div class="usp-file-name">';
		$content .= $this->get_attachment_title( $attach_id );
		$content .= '</div>';

		$content .= $this->get_attachment_manager( $attach_id );

		if ( $this->input_attach ) {
			$input_attach = $this->multiple ? $this->input_attach . '[]' : $this->input_attach;
			$content      .= '<input type="hidden" name="' . $input_attach . '" value="' . $attach_id . '">';
		}

		$content .= '</div>';

		return $content;
	}

	protected function get_src( int $attachment_id, string $size = 'attachment' ): string {

		$isImage = wp_attachment_is_image( $attachment_id );

		if ( $isImage ) {

			$fullSrc = wp_get_attachment_image_src( $attachment_id, $size );

			$fileSrc = $fullSrc[0];
		} else {
			$fileSrc = wp_get_attachment_url( $attachment_id );
		}

		return $fileSrc;
	}

	protected function add_fix_editor_buttons( array $items, int $attachment_id ): array {

		$isImage = wp_attachment_is_image( $attachment_id );

		if ( $isImage ) {

			$size = usp_get_option( 'public_form_thumb' ) ?: 'large';

			$fileHtml = wp_get_attachment_image( $attachment_id, $size, false, array( 'srcset' => ' ' ) );

			$fullSrc = wp_get_attachment_image_src( $attachment_id, 'full' );
			$fileSrc = $fullSrc[0];
		} else {

			$_post = get_post( $attachment_id );

			$fileHtml = $_post->post_title;

			$fileSrc = wp_get_attachment_url( $attachment_id );
		}

		$items[] = array(
			'icon'    => 'fa-newspaper',
			'title'   => __( 'Add to the editor', 'userspace' ),
			'onclick' => 'usp_add_attachment_in_editor(' . $attachment_id . ',"' . $this->fix_editor . '",this);return false;',
			'data'    => array(
				'html' => $fileHtml,
				'src'  => $fileSrc
			)
		);

		return $items;
	}

	protected function filter_manager_items( array $items, int $attach_id ): array {
		return $items;
	}

	protected function get_attachment_manager( int $attach_id ): ?string {

		$items = array(
			array(
				'icon'    => 'fa-trash',
				'title'   => __( 'Delete the file', 'userspace' ),
				'onclick' => 'usp_delete_attachment(' . $attach_id . ',' . $this->post_parent . ',this);return false;'
			)
		);

		$items = $this->filter_attachment_manager_items( $items, $attach_id );

		if ( $this->fix_editor ) {
			$items = $this->add_fix_editor_buttons( $items, $attach_id );
		}

		$manager_items = apply_filters( 'usp_uploader_manager_items', $items, $attach_id, $this );

		$manager_items = $this->filter_manager_items( $manager_items, $attach_id );

		if ( ! $manager_items ) {
			return null;
		}

		$content = '<div class="usp-file-manager ' . ( $this->manager_balloon ? 'usp-balloon' : '' ) . '">';

		foreach ( $manager_items as $item ) {
			$item['type'] = 'simple';
			$item['size'] = 'medium';
			$content      .= usp_get_button( $item );
		}

		$content .= '</div>';

		if ( $this->manager_balloon ) {
			$content = '<div class="attachment-manager-balloon usp-balloon-hover"><i class="uspi fa-cogs" aria-hidden="true"></i>' . $content . '</div>';
		}

		return $content;
	}

	public function upload(): ?array {

		if ( empty( $_FILES[ $this->input_name ] ) ) {
			return null;
		}

		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';

		do_action( 'usp_pre_upload', $this );

		if ( $this->dir ) {
			add_filter( 'upload_dir', [ $this, 'edit_upload_dir' ], 10 );
		}

		if ( $this->multiple ) {

			$files = [];

			//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$_passed_files = usp_recursive_map( 'sanitize_text_field', wp_unslash( $_FILES[ $this->input_name ] ) );

			foreach ( $_passed_files as $nameProp => $values ) {
				foreach ( $values as $k => $value ) {
					$files[ $k ][ $nameProp ] = $value;
				}
			}

			$uploads = array();
			foreach ( $files as $file ) {
				$uploads[] = $this->file_upload_process( $file );
			}
		} else {

			/* if ( $this->temp_media ) {
			  usp_delete_temp_media_by_args( array(
			  'uploader_id'	 => $this->uploader_id,
			  'user_id'		 => $this->user_id ? $this->user_id : 0,
			  'session_id'	 => $this->user_id ? '' : $_COOKIE['PHPSESSID'],
			  ) );
			  } */

			//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$_passed_file = usp_recursive_map( 'sanitize_text_field', wp_unslash( $_FILES[ $this->input_name ] ) );
			$uploads      = $this->file_upload_process( $_passed_file );
		}

		$this->after_upload( $uploads );

		do_action( 'usp_upload', $uploads, $this );

		return $uploads;
	}

	protected function file_upload_process( array $file ): ?array {

		$filetype = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'] );

		$ext = strtolower( $filetype['ext'] );

		if ( ! in_array( $ext, $this->file_types ) ) {
			wp_send_json( array( 'error' => __( 'Forbidden file extension. Allowed:', 'userspace' ) . ' ' . implode( ', ', $this->file_types ) ) );
		}

		$pathInfo = pathinfo( basename( $file['name'] ) );

		$file['name'] = $this->filename ? $this->filename . '.' . $ext : usp_sanitize_string( $pathInfo['filename'] ) . '.' . $ext;

		$file = apply_filters( 'usp_pre_upload_file_data', $file );

		$image = wp_handle_upload( $file, array( 'test_form' => false ) );

		if ( ! $image['file'] ) {
			return null;
		}

		$this->setup_image_sizes( $image['file'] );

		if ( $this->crop ) {

			$this->crop_image( $image['file'] );
		}

		if ( $this->resize ) {

			$this->resize_image( $image['file'] );
		}

		$attachment = array(
			'post_mime_type' => $image['type'],
			'post_title'     => $this->filetitle ? $this->filetitle : $pathInfo['filename'],
			'post_content'   => '',
			'post_excerpt'   => 'usp-uploader:' . $this->uploader_id,
			'guid'           => $image['url'],
			'post_parent'    => $this->post_parent,
			'post_author'    => $this->user_id,
			'post_status'    => 'inherit'
		);

		if ( ! $this->user_id ) {
			$session_id                 = ! empty( $_COOKIE['PHPSESSID'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['PHPSESSID'] ) ) : 'none';
			$attachment['post_content'] = $session_id;
		}

		$attach_id = wp_insert_attachment( $attachment, $image['file'], $this->post_parent );

		$attach_data = wp_generate_attachment_metadata( $attach_id, $image['file'] );

		wp_update_attachment_metadata( $attach_id, $attach_data );

		if ( $this->temp_media ) {
			usp_add_temp_media( array(
				'media_id'    => $attach_id,
				'uploader_id' => $this->uploader_id
			) );
		}

		return array(
			'id'   => $attach_id,
			'src'  => [
				'full'      => $this->get_src( $attach_id, 'full' ) . '?ver=' . current_time( 'timestamp' ),
				'thumbnail' => $this->get_src( $attach_id, 'thumbnail' ) . '?ver=' . current_time( 'timestamp' ),
			],
			'html' => $this->gallery_attachment( $attach_id )
		);
	}

	protected function setup_image_sizes( string $image_src ): void {

		if ( ! $this->image_sizes || is_array( $this->image_sizes ) ) {

			$thumbSizes = wp_get_additional_image_sizes();

			foreach ( $thumbSizes as $thumbName => $sizes ) {
				remove_image_size( $thumbName );
			}

			if ( is_array( $this->image_sizes ) ) {

				list( $width, $height ) = getimagesize( $image_src );

				foreach ( $this->image_sizes as $k => $thumbData ) {

					$thumbData = wp_parse_args( $thumbData, array(
						'width'  => $width,
						'height' => $height,
						'crop'   => 1
					) );

					add_image_size( $k . '-' . current_time( 'mysql' ), $thumbData['width'], $thumbData['height'], $thumbData['crop'] );
				}
			}
		}
	}

	protected function crop_image( string $image_src ): void {

		list( $width, $height ) = getimagesize( $image_src );

		//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$crop = ! empty( $_POST['crop_data'] ) ? usp_recursive_map( 'sanitize_text_field', wp_unslash( $_POST['crop_data'] ) ) : false;
		//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$size = ! empty( $_POST['image_size'] ) ? usp_recursive_map( 'sanitize_text_field', wp_unslash( $_POST['image_size'] ) ) : false;

		if ( ! $crop ) {
			return;
		}

		list( $crop_x, $crop_y, $crop_w, $crop_h ) = explode( ',', $crop );
		list( $viewWidth, $viewHeight ) = explode( ',', $size );

		$cf = 1;
		if ( $viewWidth < $width ) {
			$cf = $width / $viewWidth;
		}

		$crop_x *= $cf;
		$crop_y *= $cf;
		$crop_w *= $cf;
		$crop_h *= $cf;

		$image = wp_get_image_editor( $image_src );

		if ( ! is_wp_error( $image ) ) {
			$image->crop( $crop_x, $crop_y, $crop_w, $crop_h );
			$image->save( $image_src );
		}
	}

	protected function resize_image( string $image_src ): void {

		if ( ! $this->resize ) {
			return;
		}

		$image = wp_get_image_editor( $image_src );

		if ( ! is_wp_error( $image ) ) {
			$image->resize( $this->resize[0], $this->resize[1], false );
			$image->save( $image_src );
		}
	}

	public function edit_upload_dir( array $param ): array {
		$param['path'] = WP_CONTENT_DIR . untrailingslashit( $this->dir );
		$param['url']  = WP_CONTENT_URL . untrailingslashit( $this->dir );

		return $param;
	}

}
