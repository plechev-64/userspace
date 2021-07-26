<?php

class USP_Users_Manager extends USP_Content_Manager {

	public $template;
	public $custom_data;

	function __construct( $args = [] ) {

		$args = wp_parse_args( $args, [
			'number' => 30
		] );

		$this->init_custom_prop( 'template', isset( $args['template'] ) ?: 'card' );
		$this->init_custom_prop( 'custom_data', ! empty( $args['custom_data'] ) ? $args['custom_data'] : [] );

		if ( ! is_array( $this->custom_data ) ) {
			$this->custom_data = array_map( 'trim', explode( ',', $this->custom_data ) );
		}

		usp_enqueue_style( 'usp-users-' . $this->template, USP_URL . 'modules/users-list-new/assets/css/usp-users-' . $this->template . '.css', false, USP_VERSION );

		parent::
		__construct( array(
			'number'  => $args['number'],
			'is_ajax' => 1,
		) );
	}

	function get_query() {

		$select = [
			'ID',
			'display_name',
			'user_nicename',
			'user_registered',
			'last_activity' => ( new USP_User_Action( 'action' ) )->select( [ 'date_action' ] )->where_string( "users.ID=action.user_id" )
		];

		if ( in_array( 'posts', $this->custom_data ) ) {
			$select['posts'] = ( new USP_Posts_Query( 'posts' ) )->select( [
				'count' => [ 'ID' ]
			] )->where_string( "users.ID=posts.post_author" )->where( [
				'post_status'       => 'publish',
				'post_type__not_in' => [ 'page', 'nav_menu_item' ]
			] );
		}

		if ( in_array( 'comments', $this->custom_data ) ) {
			$select['comments'] = ( new USP_Comments_Query( 'comments' ) )->select( [
				'count' => [ 'comment_ID' ]
			] )->where_string( "users.ID=comments.user_id" )->where( [
				'comment_approved' => 1
			] );
		}

		$query = ( new USP_Users_Query( 'users' ) )
			->select( $select )
			->where( [
				'display_name__like' => $this->get_request_data_value( 'display_name__like' ),
				'ID__in'             => $this->get_request_data_value( 'ID__in' ),
				'ID__not_in'         => $this->get_request_data_value( 'ID__not_in' )
			] )
			->orderby(
				$this->get_request_data_value( 'orderby', 'user_registered' ),
				$this->get_request_data_value( 'order', 'DESC' )
			);

		return apply_filters( 'usp_users_query', $query, $this );
	}

	function filter_data( $data ) {

		if ( empty( $data ) ) {
			return $data;
		}

		$user_metas = [];
		$user_ids   = array_column( $data, 'ID' );

		$meta_keys = USP()->profile_fields()->get_public_fields_slugs();

		if ( $meta_keys ) {

			$metaData = ( new USP_Users_Meta_Query() )->select( [
				'meta_value',
				'meta_key',
				'user_id'
			] )->where( [
				'user_id__in'  => $user_ids,
				'meta_key__in' => $meta_keys
			] )->limit( - 1 )->get_results();

			if ( $metaData ) {
				foreach ( $metaData as $meta ) {
					$user_metas[ $meta->user_id ][ $meta->meta_key ] = maybe_unserialize( $meta->meta_value );
				}
			}

			foreach ( $data as $user ) {
				if ( isset( $user_metas[ $user->ID ] ) ) {
					$user->metadata = $user_metas[ $user->ID ];
				}
			}

		}

		foreach ( $data as $k => $user ) {
			$data[ $k ] = USP()->user( $user->ID )->setup( $user );
		}

		return apply_filters( 'usp_users_data', $data, $this );

	}

	function get_item_content( $user ) {
		return usp_get_include_template( 'user-' . $this->template . '.php', USP_USERS_BASE, [
			'user'        => $user,
			'custom_data' => $this->custom_data
		] );
	}

	function get_search_fields() {

		$orderby_values = [ 'user_registered' => __( 'Дата регистрации', 'wp-recall' ) ];

		if ( in_array( 'comments', $this->custom_data ) ) {
			$orderby_values['comments'] = __( 'Количеству комментариев', 'wp-recall' );
		}

		if ( in_array( 'posts', $this->custom_data ) ) {
			$orderby_values['posts'] = __( 'Количеству публикаций', 'wp-recall' );
		}

		$search_fields = [
			[
				'type'  => 'text',
				'slug'  => 'display_name__like',
				'title' => __( 'Поиск' ),
				'value' => $this->get_request_data_value( 'display_name__like' ),
			],
			[
				'type'   => 'select',
				'slug'   => 'orderby',
				'title'  => __( 'Сортировка по' ),
				'values' => $orderby_values,
				'value'  => $this->get_request_data_value( 'orderby', 'user_registered' ),
			],
			[
				'type'   => 'radio',
				'slug'   => 'order',
				'title'  => __( 'Направление сортировки' ),
				'values' => [
					'DESC' => __( 'По убыванию' ),
					'ASC'  => __( 'По возрастанию' )
				],
				'value'  => $this->get_request_data_value( 'order', 'DESC' ),
			]
		];

		return apply_filters( 'usp_users_search_fields', $search_fields, $this );

	}

}