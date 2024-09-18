<?php

class UserActionsQuery extends QueryBuilder {
	function __construct( $as = false ) {

		$table = [
			'name' => USP_PREF . 'users_actions',
			'as'   => $as ? $as : 'usp_user_action',
			'cols' => [
				'actid',
				'user_id',
				'date_action'
			]
		];

		parent::__construct( $table );
	}

}