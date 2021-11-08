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
