<?php

namespace USP\Core\Query\DefaultTable;

use USP\Core\Query\QueryBuilder;

class CommentsQuery extends QueryBuilder {

	function __construct( $as = false ) {
		global $wpdb;

		$table = [
			'name' => $wpdb->comments,
			'as'   => $as ?: 'wp_comments',
			'cols' => [
				'comment_ID',
				'comment_post_ID',
				'comment_content',
				'comment_approved',
				'comment_date',
				'comment_author',
				'user_id'
			]
		];

		parent::__construct( $table );
	}

}