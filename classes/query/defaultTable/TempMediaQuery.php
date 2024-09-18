<?php

class TempMediaQuery extends QueryBuilder {
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