<?php

namespace USP\Core\Query\DefaultTable;

use USP\Core\Query\QueryBuilder;

class PostsQuery extends QueryBuilder {
	function __construct( $as = false ) {
		global $wpdb;

		$table = [
			'name' => $wpdb->posts,
			'as'   => $as ?: 'wp_posts',
			'cols' => [
				'ID',
				'post_author',
				'post_status',
				'post_type',
				'post_date',
				'post_modified',
				'post_title',
				'post_content',
				'post_excerpt',
				'post_parent',
				'post_name',
				'post_mime_type',
				'guid',
				'comment_count',
				'comment_status'
			]
		];

		parent::__construct( $table );
	}

}