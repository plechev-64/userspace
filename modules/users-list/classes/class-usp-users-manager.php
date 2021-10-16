<?php


class USP_Users_Manager extends USP_Content_Manager {

	private $_required_params = [
		'number'   => 10,
		'orderby'  => 'user_registered',
		'order'    => 'DESC',
		'pagenavi' => 0,
		'search'   => 0
	];

	private $_custom_params = [
		'template'                => 'rows',
		'custom_data'             => [],
		/*
		 * Main query where
		 */
		'id__not_in'              => [],
		'id__in'                  => [],
		'user_registered__from'   => null,
		'user_registered__to'     => null,
		'posts__from'             => null,
		'posts__to'               => null,
		'comments__from'          => null,
		'comments__to'            => null,
		'last_activity__from'     => null,
		'last_activity__to'       => null,
		'online_only'             => null,
		/*
		 * Calc post count query
		 */
		'post_type__in'           => [],
		'post_type__not_in'       => [ 'page', 'nav_menu_item' ],
		'post_status__in'         => [ 'publish' ],
		'post_status__not_in'     => [],
		/*
		 * Calc comments count query
		 */
		'comment_type__in'        => [],
		'comment_type__not_in'    => [],
		'comment_post_id__in'     => [],
		'comment_post_id__not_in' => []
	];

	function __construct( $args = [] ) {

		//convert shortcode val to array
		foreach ( $args as $k => $v ) {

			if ( isset( $this->_custom_params[ $k ] ) ) {

				if ( is_array( $this->_custom_params[ $k ] ) && ! is_array( $v ) ) {
					$args[ $k ] = array_map( 'trim', explode( ',', $v ) );
				}

			}

		}

		$this->set_custom_default_params( $this->_custom_params );

		parent::__construct( array_merge( $this->_required_params, $args ) );

		$this->enqueue_assets();
	}

	function enqueue_assets() {

		if ( $this->get_param( 'template' ) == 'masonry' ) {
			usp_masonry_script();
		}

		if ( in_array( $this->get_param( 'template' ), [ 'rows', 'masonry', 'full', 'card' ] ) ) {
			usp_enqueue_style(
				'usp-users-' . $this->get_param( 'template' ),
				USP_URL . 'modules/users-list/assets/css/usp-users-' . $this->get_param( 'template' ) . '.css'
			);
		}

	}

	function get_query() {

		$select = [
			'ID',
			'display_name',
			'user_nicename',
			'user_registered'
		];

		$query = ( new USP_Users_Query( 'users' ) )
			->select( $select )
			->where( [
				'display_name__like'    => $this->get_param( 'display_name__like' ),
				'ID__in'                => $this->get_param( 'id__in' ),
				'ID__not_in'            => $this->get_param( 'id__not_in' ),
				'user_registered__from' => $this->get_param( 'user_registered__from' ),
				'user_registered__to'   => $this->get_param( 'user_registered__to' )
			] );

		$query = $this->join_last_activity( $query );

		if ( $this->get_param( 'online_only' ) ) {

			$timeout          = usp_get_option( 'usp_user_timeout', 10 ) * 60;
			$online_only_date = date( "Y-m-d h:i:s", current_time( 'timestamp' ) - $timeout );
			$query->where_string( "action.date_action > '{$online_only_date}'" );

		} else {

			if ( $this->get_param( 'last_activity__from' ) ) {
				$query->where_string( 'action.date_action > "' . date( "Y-m-d h:i:s", strtotime( $this->get_param( 'last_activity__from' ) ) ) . '"' );
			}
			if ( $this->get_param( 'last_activity__to' ) ) {
				$query->where_string( 'action.date_action < "' . date( "Y-m-d h:i:s", strtotime( $this->get_param( 'last_activity__to' ) ) ) . '"' );
			}
		}

		if ( in_array( 'posts', $this->get_param( 'custom_data' ) ) ) {
			$query = $this->join_posts( $query );

			if ( $this->get_param( 'posts__from' ) ) {
				$query->where_string( "wp_posts.posts_count > " . ( (int) $this->get_param( 'posts__from' ) ) );
			}
			if ( $this->get_param( 'posts__to' ) ) {
				$query->where_string( "wp_posts.posts_count < " . ( (int) $this->get_param( 'posts__to' ) ) );
			}
		}

		if ( in_array( 'comments', $this->get_param( 'custom_data' ) ) ) {
			$query = $this->join_comments( $query );

			if ( $this->get_param( 'comments__from' ) ) {
				$query->where_string( "wp_comments.comments_count > " . ( (int) $this->get_param( 'comments__from' ) ) );
			}
			if ( $this->get_param( 'comments__to' ) ) {
				$query->where_string( "wp_comments.comments_count < " . ( (int) $this->get_param( 'comments__to' ) ) );
			}
		}

		return apply_filters( 'usp_users_query', $query, $this );
	}

	private function join_last_activity( $query ) {

		$query->join(
			[ 'ID', 'user_id', 'LEFT' ],
			( new USP_User_Action( 'action' ) )
				->select( [ 'last_activity' => 'date_action' ] )
		);

		return $query;
	}

	private function join_posts( USP_Query $query ) {

		$posts_query = ( new USP_Posts_Query( 'wp_posts' ) )
			->select( [
				'count'       => [ 'posts_count' => 'ID' ],
				'post_author' => 'post_author'
			] )
			->where( [
				'post_status__in'     => $this->get_param( 'post_status__in' ),
				'post_status__not_in' => $this->get_param( 'post_status__not_in' ),
				'post_type__in'       => $this->get_param( 'post_type__in' ),
				'post_type__not_in'   => $this->get_param( 'post_type__not_in' ),
				'post_author__in'     => $this->get_param( 'id__in' ),
				'post_author__not_in' => $this->get_param( 'id__not_in' )
			] )
			->groupby( 'post_author' )
			->limit( - 1 );

		$posts_sql = $posts_query->get_sql();

		$query->select_string( 'IFNULL(wp_posts.posts_count, 0) as posts' );
		$query->join_string( "LEFT JOIN ({$posts_sql}) as wp_posts ON users.ID = wp_posts.post_author" );

		return $query;
	}

	private function join_comments( USP_Query $query ) {

		$comments_query = ( new USP_Comments_Query( 'wp_comments' ) )
			->select( [
				'count'          => [ 'comments_count' => 'comment_ID' ],
				'comment_author' => 'user_id'
			] )
			->where( [
				'comment_approved'        => 1,
				'comment_type__not_in'    => $this->get_param( 'comment_type__not_in' ),
				'comment_post_ID__in'     => $this->get_param( 'comment_post_id__in' ),
				'comment_post_ID__not_in' => $this->get_param( 'comment_post_id__not_in' ),
				'user_id__in'             => $this->get_param( 'id__in' ),
				'user_id__not_in'         => $this->get_param( 'id__not_in' )
			] )
			->groupby( 'user_id' )
			->limit( - 1 );


		$comments_sql = $comments_query->get_sql();

		$query->select_string( 'IFNULL(wp_comments.comments_count, 0) as comments' );
		$query->join_string( "LEFT JOIN ({$comments_sql}) as wp_comments ON users.ID = wp_comments.comment_author" );

		return $query;
	}

	function filter_items( $users ) {

		if ( empty( $users ) ) {
			return $users;
		}

		$user_metas = [];
		$avatar_ids = [];
		$user_ids   = array_column( $users, 'ID' );

		$meta_keys   = USP()->profile_fields()->get_public_fields_slugs();
		$meta_keys[] = 'usp_avatar';

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
				if ( $meta->meta_key === 'usp_avatar' ) {
					$avatar_ids[] = $meta->meta_value;
				}
			}
		}

		$avatars          = $avatar_ids ? OptAttachments::setup_attachments( $avatar_ids ) : [];
		$default_metaData = array_fill_keys( $meta_keys, '' );

		foreach ( $users as $user ) {
			$user->metadata = $default_metaData;
			if ( isset( $user_metas[ $user->ID ] ) ) {
				$user->metadata = array_merge( $user->metadata, $user_metas[ $user->ID ] );
				if ( $avatars && ! empty( $user->metadata['usp_avatar'] ) && $avatars->is_has( $user->metadata['usp_avatar'] ) ) {
					$user->avatar = $avatars->attachment( $user->metadata['usp_avatar'] );
				}
			}
		}

		foreach ( $users as $k => $user ) {
			$users[ $k ] = USP()->user( $user->ID )->setup( $user );
		}

		return apply_filters( 'usp_users_data', $users, $this );
	}

	function get_search_fields() {

		if ( ! $this->get_param( 'search' ) ) {
			return [];
		}

		$orderby_values = [ 'user_registered' => __( 'Registration date', 'userspace' ) ];

		if ( in_array( 'comments', $this->get_param( 'custom_data' ) ) ) {
			$orderby_values['comments'] = __( 'Comments count', 'userspace' );
		}

		if ( in_array( 'posts', $this->get_param( 'custom_data' ) ) ) {
			$orderby_values['posts'] = __( 'Publications count', 'userspace' );
		}

		$search_fields = [
			[
				'type'  => 'text',
				'slug'  => 'display_name__like',
				'title' => __( 'Search', 'userspace' ),
				'value' => $this->get_param( 'display_name__like' ),
			],
			[
				'type'   => 'select',
				'slug'   => 'orderby',
				'title'  => __( 'Sort by', 'userspace' ),
				'values' => $orderby_values,
				'value'  => $this->get_param( 'orderby' ),
			],
			[
				'type'   => 'radio',
				'slug'   => 'order',
				'title'  => __( 'Sorting direction', 'userspace' ),
				'values' => [
					'DESC' => __( 'Descending', 'userspace' ),
					'ASC'  => __( 'Ascending', 'userspace' )
				],
				'value'  => $this->get_param( 'order' ),
			]
		];

		return apply_filters( 'usp_users_search_fields', $search_fields, $this );

	}

	public function get_items_content() {

		$items = $this->get_items();

		$content = '<div class="usp-content-manager-content">';

		if ( ! $items ) {
			$content .= $this->get_no_result_notice();
		} else {

			$data_masonry = ( $this->get_param( 'template' ) === 'masonry' ) ? 'data-columns="3"' : '';

			$content .= '<div class="usp-users usps usp-users-' . $this->get_param( 'template' ) . '" ' . $data_masonry . '>';

			foreach ( $items as $item ) {
				$content .= $this->get_item_content( $item );
			}

			$content .= '</div>';
		}
		$content .= '</div>';

		if ( $this->get_param( 'template' ) == 'masonry' && usp_is_ajax() ) {
			$content .= "<script>salvattore.init();</script>";
		}

		return $content;
	}

	public function get_item_content( $user ) {
		return usp_get_include_template( 'user-' . $this->get_param( 'template' ) . '.php', USP_USERS_BASE, [
			'user'        => $user,
			'custom_data' => $this->get_param( 'custom_data' )
		] );
	}

}