<?php

// registers the sizes of avatars
add_action( 'wp_head', 'usp_register_avatar_sizes', 10 );
function usp_register_avatar_sizes() {
	/**
	 * Filter allows you to add sizes of avatars.
	 *
	 * @param   $data   array   Data avatar sizes.
	 *                          Default: 70, 150, 300
	 *
	 * @since   1.0.0
	 */
	$sizes = apply_filters( 'usp_avatar_sizes', [ 70, 150, 300 ] );

	asort( $sizes );

	foreach ( $sizes as $k => $size ) {
		add_image_size( 'usp-avatar-' . $size, $size, $size, 1 );
	}
}

// specifying the url before the uploaded avatar image
add_filter( 'pre_get_avatar_data', 'usp_avatar_data_replacement', 20, 2 );
function usp_avatar_data_replacement( $args, $id_or_email ) {
	// todo: $usp_user у нас нет глобальной, надо заменить на объект юзера?
	global $usp_user;

	$size = $args['size'];

	$user_id     = 0;
	$avatar_data = false;

	if ( $usp_user && $usp_user->ID == $id_or_email ) {

		$user_id = $usp_user->ID;

		if ( isset( $usp_user->avatar_data ) && $usp_user->avatar_data ) {
			$avatar_data = $usp_user->avatar_data;
		}
	} else {

		if ( is_numeric( $id_or_email ) ) {
			$user_id = $id_or_email;
		} elseif ( is_object( $id_or_email ) ) {
			$user_id = $id_or_email->user_id;
		} elseif ( is_email( $id_or_email ) ) {
			$user = get_user_by( 'email', $id_or_email );
			if ( $user ) {
				$user_id = $user->ID;
			}
		}
	}

	if ( $user_id ) {

		if ( ! $avatar_data ) {
			$avatar_data = get_user_meta( $user_id, 'usp_avatar', 1 );
		}

		if ( ! $avatar_data ) {
			$avatar_data = usp_get_option( 'usp_default_avatar' );
		}

		if ( $avatar_data ) {

			$url = false;

			if ( is_numeric( $avatar_data ) ) {
				$image_attributes = wp_get_attachment_image_src( $avatar_data, [ $size, $size ] );
				if ( $image_attributes ) {
					$url = $image_attributes[0];
				}
			}

			if ( $url && file_exists( usp_path_by_url( $url ) ) ) {
				$args['url'] = $url;
			}
		}
	}

	return $args;
}

/**
 * Return menu object for user avatar
 *
 * @param User $user
 *
 * @return DropdownMenu
 */
function usp_get_user_avatar_menu( User $user ) {

	$menu = new DropdownMenu( 'usp_user_avatar_menu', [
		'custom_data' => [
			'user' => $user
		],
		'open_button' => [
			'icon' => 'fa-vertical-ellipsis',
			'size' => 'medium'
		],
	] );

	$menu->add_button( [
		'label'   => __( 'User info', 'userspace' ),
		'onclick' => 'usp_get_user_info(this);return false;',
		'icon'    => 'fa-info-circle'
	] );

	$menu->add_button( [
		'class'   => 'usp-ava__zoom',
		'label'   => __( 'Zoom avatar', 'userspace' ),
		'onclick' => 'usp_zoom_user_avatar(this);return false;',
		'data'    => [
			'zoom' => get_avatar_url( $user->ID, [ 'size' => 1000 ] )
		],
		'icon'    => 'fa-search',
	] );

	if ( get_current_user_id() == $user->ID ) {

		$avatar_uploader = new Uploader( 'usp_avatar', [
			'multiple'    => 0,
			'crop'        => 1,
			'filetitle'   => 'usp-user-avatar-' . $user->ID,
			'filename'    => $user->ID,
			'dir'         => '/uploads/usp-uploads/avatars',
			'image_sizes' => [
				[
					'height' => 70,
					'width'  => 70,
					'crop'   => 1
				],
				[
					'height' => 150,
					'width'  => 150,
					'crop'   => 1
				],
				[
					'height' => 300,
					'width'  => 300,
					'crop'   => 1
				]
			],
			'resize'      => [ 1000, 1000 ],
			'min_height'  => 150,
			'min_width'   => 150,
			'max_size'    => usp_get_option( 'usp_avatar_weight', 1024 )
		] );

		$menu->add_button( [
			'class'   => 'usp-ava__uploads',
			'label'   => __( 'Upload avatar', 'userspace' ),
			'content' => $avatar_uploader->get_input(),
			'icon'    => 'fa-download'
		] );

		if ( $user->usp_avatar ) {
			$menu->add_button( [
				'label' => __( 'Delete avatar', 'userspace' ),
				'href'  => wp_nonce_url( add_query_arg( [ 'usp-action' => 'delete_avatar' ], $user->get_url() ), $user->ID ),
				'icon'  => 'fa-times'
			] );

		}

	}

	if ( get_current_user_id() == $user->ID ) {

		$uploader_cover = new Uploader( 'usp_cover', [
			'multiple'    => 0,
			'filetitle'   => 'usp-user-cover-' . $user->ID,
			'filename'    => $user->ID,
			'dir'         => '/uploads/usp-uploads/covers',
			'crop'        => [
				'ratio' => 0
			],
			'image_sizes' => [
				[
					'height' => 9999,
					'width'  => 9999,
					'crop'   => 0
				]
			],
			'resize'      => [ 1500, 1500 ],
			'min_height'  => 300,
			'min_width'   => 600,
			'max_size'    => usp_get_option( 'usp_cover_weight', 1024 )
		] );

		$menu->add_button( [
			'class'   => 'usp-cover__uploads',
			'label'   => __( 'Upload cover', 'userspace' ),
			'content' => $uploader_cover->get_input(),
			'icon'    => 'fa-image',
			'id'      => 'usp-cover-upload'
		] );
	}

	return $menu;

}