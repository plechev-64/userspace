<?php

class UsersQuery extends QueryBuilder {
	function __construct( $as = false ) {
		global $wpdb;

		$table = [
			'name' => $wpdb->users,
			'as'   => $as ? $as : 'wp_users',
			'cols' => [
				'ID',
				'user_login',
				'user_email',
				'user_registered',
				'display_name',
				'user_nicename'
			]
		];

		parent::__construct( $table );
	}

}