<?php


class USP_Users_Manager extends USP_Content_Manager {

	private $_default_args = [
		'number'          => 10,
		'template'        => 'rows',
		'custom_data'     => [],
		'dropdown_filter' => 0,
		'pagenavi'        => 1,
		'orderby'         => 'user_registered',
		'order'           => 'DESC',
		'search'          => 1,
		'ID__not_in'      => [],
		'ID__in'          => []
	];

	function __construct( $args = [] ) {

		$args = wp_parse_args( $args, $this->_default_args );

		foreach ( $args as $param => $value ) {

			$this->init_custom_prop( $param, $value );

			/*
			 * if default value for param - array, convert to array
			 */
			if ( is_array( $this->_default_args[ $param ] ) && ! is_array( $this->$param ) ) {
				$this->$param = $this->$param ? array_map( 'trim', explode( ',', $this->$param ) ) : [];
			}
		}

		$this->prepare_params();
		$this->enqueue_assets();

		parent::
		__construct( array(
			'is_ajax' => 1,
		) );
	}

	function prepare_params() {

		if ( $this->ID__in ) {
			$this->pagenavi = 0;
			$this->number   = count( $this->ID__in );
		}

		if ( ! $this->search ) {
			$this->reset_filter = false;
		}

	}

	function enqueue_assets() {

		if ( $this->template == 'masonry' ) {
			usp_masonry_script();
		}

		if ( in_array( $this->template, [ 'rows', 'masonry', 'full', 'card' ] ) ) {
			usp_enqueue_style(
				'usp-users-' . $this->template,
				USP_URL . 'modules/users-list/assets/css/usp-users-' . $this->template . '.css'
			);
		}

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
				'ID__in'             => $this->ID__in,
				'ID__not_in'         => $this->ID__not_in
			] )
			->orderby(
				$this->orderby,
				$this->order
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

		$meta_keys[] = 'usp_avatar';

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

			$avatar_ids = [];
			foreach ( $data as $user ) {
				if ( isset( $user_metas[ $user->ID ] ) ) {
					$user->metadata = $user_metas[ $user->ID ];

					if ( ! empty( $user->metadata['usp_avatar'] ) ) {
						$avatar_ids[] = $user->metadata['usp_avatar'];
					}
				}
			}

			$avatars = [];
			if ( $avatar_ids ) {
				$avatars = OptAttachments::setup_attachments( $avatar_ids );
			}

			foreach ( $data as $user ) {
				if ( isset( $user_metas[ $user->ID ] ) ) {
					$user->metadata = $user_metas[ $user->ID ];
					if ( $avatars && ! empty( $user->metadata['usp_avatar'] ) && $avatars->is_has( $user->metadata['usp_avatar'] ) ) {
						$user->avatar = $avatars->attachment( $user->metadata['usp_avatar'] );
					}
				}
			}

		}

		foreach ( $data as $k => $user ) {
			$data[ $k ] = USP()->user( $user->ID )->setup( $user );
		}

		return apply_filters( 'usp_users_data', $data, $this );

	}

	function get_data_content() {

		$data_masonry = ( $this->template === 'masonry' ) ? 'data-columns="3"' : '';

		$content = '<div class="manager-content">';

		if ( ! $this->data ) {
			$content .= $this->get_no_result_notice();
		} else {
			$content .= '<div class="usp-users usps usp-users-' . $this->template . '" ' . $data_masonry . '>';

			foreach ( $this->data as $dataItem ) {
				$content .= $this->get_item_content( $dataItem );
			}

			$content .= '</div>';
		}
		$content .= '</div>';

		if ( $this->template == 'masonry' && usp_is_ajax() ) {
			$content .= "<script>salvattore.init();</script>";
		}

		return $content;
	}

	function get_item_content( $user ) {
		return usp_get_include_template( 'user-' . $this->template . '.php', USP_USERS_BASE, [
			'user'        => $user,
			'custom_data' => $this->custom_data
		] );
	}

	function get_search_fields() {

		if ( ! $this->search ) {
			return [];
		}

		$orderby_values = [ 'user_registered' => __( 'Registration date', 'userspace' ) ];

		if ( in_array( 'comments', $this->custom_data ) ) {
			$orderby_values['comments'] = __( 'Comments count', 'userspace' );
		}

		if ( in_array( 'posts', $this->custom_data ) ) {
			$orderby_values['posts'] = __( 'Publics count', 'userspace' );
		}

		$search_fields = [
			[
				'type'  => 'text',
				'slug'  => 'display_name__like',
				'title' => __( 'Search', 'userspace' ),
				'value' => $this->get_request_data_value( 'display_name__like' ),
			],
			[
				'type'   => 'select',
				'slug'   => 'orderby',
				'title'  => __( 'Sort by', 'userspace' ),
				'values' => $orderby_values,
				'value'  => $this->get_request_data_value( 'orderby', $this->orderby ),
			],
			[
				'type'   => 'radio',
				'slug'   => 'order',
				'title'  => __( 'Sorting direction', 'userspace' ),
				'values' => [
					'DESC' => __( 'Descending', 'userspace' ),
					'ASC'  => __( 'Ascending', 'userspace' )
				],
				'value'  => $this->get_request_data_value( 'order', $this->order ),
			]
		];

		return apply_filters( 'usp_users_search_fields', $search_fields, $this );

	}

}