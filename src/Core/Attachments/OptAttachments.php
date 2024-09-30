<?php

namespace USP\Core\Attachments;

class OptAttachments {

	private array $attachments;

	public function __construct( array $attachments ) {
		$this->attachments = $attachments;
	}

	public function is_has( int $id ): bool {
		return isset( $this->attachments[ $id ] );
	}

	public function attachment( int $id ): OptAttachment {
		return $this->attachments[ $id ];
	}

	public function get_thumbnail_image( int $post_id, string|array $size, array|string $atts = [] ): bool|string {

		if ( ! $this->is_has( $post_id ) ) {
			return false;
		}

		if ( $image = $this->attachment( $post_id )->get_image( $size, $atts ) ) {
			return $image;
		} else {
			return get_the_post_thumbnail( $post_id, $size, $atts );
		}
	}

	static function setup_post_thumbnails_by_object(
		array $arrayObjects,
		string $propName,
		string $meta_key = '_thumbnail_id',
		bool $get_attached_file = false
	): OptAttachments {

		$post_ids = [];
		foreach ( $arrayObjects as $object ) {
			$post_ids[] = $object->$propName;
		}

		return self::setup_post_thumbnails( $post_ids );

	}

	static function setup_post_thumbnails(
		array $post_ids,
		string $meta_key = '_thumbnail_id',
		bool $get_attached_file = false
	): OptAttachments {

		$attachments = [];
		if ( $thumbnails = self::get_metadata_thumbnails( $post_ids, $meta_key, $get_attached_file ) ) {
			foreach ( $thumbnails as $thumbnail ) {
				$attachments[ $thumbnail->post_id ] = new OptAttachment( $thumbnail->attach_id, maybe_unserialize( $thumbnail->metadata ), !empty($thumbnail->attached_file) );
			}
		}

		return new OptAttachments( $attachments );

	}

	static function setup_attachments( array $attach_ids ): OptAttachments {

		$attachments = [];
		if ( $thumbnails = self::get_metadata_attachments( $attach_ids ) ) {
			foreach ( $thumbnails as $thumbnail ) {
				$attachments[ $thumbnail->attach_id ] = new OptAttachment( $thumbnail->attach_id, maybe_unserialize( $thumbnail->metadata ) );
			}
		}

		return new OptAttachments( $attachments );

	}

	static function get_query_attachments_request( array $attach_ids ): PostsMetaQuery {

		return ( new PostsMetaQuery() )->select( [
			'attach_id' => 'post_id',
			'metadata'  => 'meta_value'
		] )->where( [
			'meta_key'    => '_wp_attachment_metadata',
			'post_id__in' => array_diff( array_unique( $attach_ids ), [ '' ] ),
		] )->limit( - 1 );

	}

	static function get_metadata_attachments( array $attach_ids ): array {
		return self::get_query_attachments_request( $attach_ids )->get_results();
	}

	static function get_query_thumbnails_request( array $post_ids, string $meta_key = '_thumbnail_id', bool $get_attached_file = false ): PostsMetaQuery {

		$query = ( new PostsMetaQuery() )->select( [
			'post_id',
			'attach_id' => 'meta_value'
		] )->where( [
			'meta_key'    => $meta_key,
			'post_id__in' => $post_ids,
		] )->join(
			[ 'meta_value', 'post_id' ],
			( new PostsMetaQuery( 'metadata' ) )->select( [ 'metadata' => 'meta_value' ] )->where( [
				'meta_key' => '_wp_attachment_metadata'
			] )
		)->limit( - 1 );

		if ( $get_attached_file ) {
			$query->join(
				[ 'meta_value', 'post_id' ],
				( new PostsMetaQuery( 'file' ) )->select( [ 'attached_file' => 'meta_value' ] )->where( [
					'meta_key' => '_wp_attached_file'
				] )
			);
		}

		return $query;

	}

	static function get_metadata_thumbnails( array $post_ids, string $meta_key = '_thumbnail_id', bool $get_attached_file = false ): array {
		return self::get_query_thumbnails_request( $post_ids, $meta_key, $get_attached_file )->get_results();
	}

}
