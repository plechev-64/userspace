<?php

class USP_Users_Manager extends USP_Content_Manager {

	public $template;
	public $counters;
	public $meta;

	function __construct( $args = [] ) {

		$args = wp_parse_args( $args, [
			'number' => 30
		] );

		if ( ! empty( $args['counter'] ) && ! is_array( $args['counter'] ) ) {
			$args['counter'] = array_map( 'trim', explode( ',', $args['counter'] ) );
		}

		if ( ! empty( $args['meta'] ) && ! is_array( $args['meta'] ) ) {
			$args['meta'] = array_map( 'trim', explode( ',', $args['meta'] ) );
		}
		
		$this->init_custom_prop( 'template', isset( $args['template'] ) ?: 'card' );
		$this->init_custom_prop( 'counter', ! empty( $args['counter'] ) ? $args['counter'] : [] );
		$this->init_custom_prop( 'meta', ! empty( $args['meta'] ) ? $args['meta'] : [] );

		usp_enqueue_style( 'usp-users-'.$this->template, USP_URL . 'modules/users-list-new/assets/css/usp-users-'.$this->template.'.css', false, USP_VERSION );

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
			'last_activity' => ( new USP_User_Action( 'action' ) )->select( ['date_action'] )->where_string( "users.ID=action.user_id" )
		];

		if ( $this->counter ) {

			if ( in_array( 'posts', $this->counter ) ) {
				$select['posts'] = ( new USP_Posts_Query( 'posts' ) )->select( [
					'count' => [ 'ID' ]
				] )->where_string( "users.ID=posts.post_author" )->where( [
					'post_status'       => 'publish',
					'post_type__not_in' => [ 'page', 'nav_menu_item' ]
				] );
			}

			if ( in_array( 'comments', $this->counter ) ) {
				$select['comments'] = ( new USP_Comments_Query( 'comments' ) )->select( [
					'count' => [ 'comment_ID' ]
				] )->where_string( "users.ID=comments.user_id" )->where( [
					'comment_approved' => 1
				] );
			}

		}

		$query = ( new USP_Users_Query( 'users' ) )
		->select( $select )
		->where( [
			'ID__in'     => $this->get_request_data_value( 'ID__in' ),
			'ID__not_in' => $this->get_request_data_value( 'ID__not_in' )
		] )
		->orderby(
			$this->get_request_data_value( 'orderby', 'user_registered' ),
			$this->get_request_data_value( 'order', 'DESC' )
		);

		return apply_filters( 'usp_users_query', $query, $this );
	}

	function filter_data($data){

		if(empty($data)){
			return $data;
		}

		$user_metas = [];
		$user_ids = [];
		foreach($data as $user){
			$user_ids[] = $user->ID;
		}

		if ( $this->meta ){

			$metaData = (new USP_Users_Meta_Query())->select([
				'meta_value', 'meta_key', 'user_id'
			])->where([
				'user_id__in' => $user_ids,
				'meta_key__in' => $this->meta
			])->limit(-1)->get_results();

			if($metaData){
				foreach($metaData as $meta){
					$user_metas[$meta->user_id][$meta->meta_key] = maybe_unserialize($meta->meta_value);
				}
			}

		}

		foreach($data as $user){
			if(isset($user_metas[$user->ID])){
				$user->metadata = $user_metas[$user->ID];
			}
		}

		return $data;

	}

	function get_item_content( $user ) {
		return usp_get_include_template( 'user-' . $this->template . '.php', USP_USERS_BASE, [
			'user' => $user
		] );
	}

	function get_search_fields() {

		return false;

		/*return [
			array(
				'type'  => 'text',
				'slug'  => 'post_title',
				'title' => __( 'Наименование' ),
				'value' => $this->get_request_data_value( 'post_title' ),
			),
		];*/

		return array(
			array(
				'type'  => 'text',
				'slug'  => 'post_title',
				'title' => __( 'Наименование' ),
				'value' => $this->get_request_data_value( 'post_title' ),
			),
			array(
				'type'  => 'number',
				'slug'  => 'ID',
				'title' => __( 'ID публикации' ),
				'value' => $this->get_request_data_value( 'ID' ),
			),
			array(
				'type'  => 'number',
				'slug'  => 'post_author',
				'title' => __( 'Автор публикации' ),
				'value' => $this->get_request_data_value( 'post_author' ),
			),
			array(
				'type'   => 'select',
				'slug'   => 'orderby',
				'title'  => __( 'Сортировка по' ),
				'values' => [
					'post_date'     => __( 'Дате создания', 'wp-recall' ),
					'comment_count' => __( 'Количеству комментариев', 'wp-recall' )
				],
				'value'  => $this->get_request_data_value( 'orderby', 'post_date' ),
			),
			array(
				'type'   => 'radio',
				'slug'   => 'order',
				'title'  => __( 'Направление сортировки' ),
				'values' => [
					'DESC' => __( 'По убыванию' ),
					'ASC'  => __( 'По возрастанию' )
				],
				'value'  => $this->get_request_data_value( 'order', 'DESC' ),
			)
		);

	}

}