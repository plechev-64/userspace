<?php

namespace USP\Core\Query\DefaultTable;

use USP\Core\Query\QueryBuilder;

class UsersMetaQuery extends QueryBuilder {

	function __construct( $as = false ) {
		global $wpdb;

		$table = [
			'name' => $wpdb->usermeta,
			'as'   => $as ?: 'wp_usermeta',
			'cols' => [
				'umeta_id',
				'user_id',
				'meta_key',
				'meta_value'
			]
		];

		parent::__construct( $table );

	}

}