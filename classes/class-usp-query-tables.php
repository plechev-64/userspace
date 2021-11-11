<?php

class USP_Users_Query extends USP_Query {
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

class USP_Users_Meta_Query extends USP_Query {

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

class USP_Posts_Query extends USP_Query {
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

class USP_Posts_Meta_Query extends USP_Query {

	public $serialize = [ 'meta_value' ];

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

class USP_Comments_Query extends USP_Query {

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

class USP_User_Action extends USP_Query {
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

class USP_Temp_Media extends USP_Query {
	function __construct( $as = false ) {

		$table = [
			'name' => USP_PREF . 'temp_media',
			'as'   => $as ? $as : 'usp_temp_media',
			'cols' => [
				'media_id',
				'user_id',
				'uploader_id',
				'session_id',
				'upload_date'
			]
		];

		parent::__construct( $table );
	}

}

class USP_Blacklist extends USP_Query {
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
