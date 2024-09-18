<?php

class PostsMetaQuery extends QueryBuilder {

	public array $serialize = [ 'meta_value' ];

	function __construct( $as = false ) {
		global $wpdb;

		$table = [
			'name' => $wpdb->postmeta,
			'as'   => $as ?: 'wp_postmeta',
			'cols' => [
				'post_id',
				'meta_key',
				'meta_value'
			]
		];

		parent::__construct( $table );
	}

}