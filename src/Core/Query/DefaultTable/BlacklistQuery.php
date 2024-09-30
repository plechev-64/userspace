<?php

namespace USP\Core\Query\DefaultTable;

use USP\Core\Query\QueryBuilder;

class BlacklistQuery extends QueryBuilder {
	function __construct( $as = false ) {

		$table = [
			'name' => USP_PREF . 'blacklist',
			'as'   => $as ? $as : 'usp_blacklist',
			'cols' => [
				'ID',
				'user_id',
				'blocked'
			]
		];

		parent::__construct( $table );
	}

}