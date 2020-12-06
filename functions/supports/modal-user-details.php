<?php

if ( ! is_admin() ):
	add_action( 'usp_enqueue_scripts', 'usp_support_user_info_scripts', 10 );
endif;
function usp_support_user_info_scripts() {
	if ( usp_is_office() ) {
		usp_dialog_scripts();
		usp_enqueue_script( 'usp-user-info', USP_URL . 'functions/supports/js/user-details.js' );
	}
}

add_filter( 'usp_init_js_variables', 'usp_init_js_user_info_variables', 10 );
function usp_init_js_user_info_variables( $data ) {

	if ( usp_is_office() ) {
		$data['local']['title_user_info'] = __( 'Detailed information', 'usp' );
	}

	return $data;
}

add_filter( 'usp_avatar_icons', 'usp_add_user_info_button', 10 );
function usp_add_user_info_button( $icons ) {

	usp_dialog_scripts();

	$icons['user-info'] = array(
		'icon'	 => 'fa-info-ciuspe',
		'atts'	 => array(
			'title'		 => __( 'User info', 'usp' ),
			'onclick'	 => 'usp_get_user_info(this);return false;',
			'url'		 => '#'
		)
	);

	return $icons;
}

usp_ajax_action( 'usp_return_user_details', true );
function usp_return_user_details() {

	return array(
		'content' => usp_get_user_details( intval( $_POST['user_id'] ) )
	);
}

function usp_get_user_details( $user_id, $args = false ) {
	global $user_LK, $usp_blocks;

	$user_LK = $user_id;

	$defaults = array(
		'zoom'			 => true,
		'description'	 => true,
		'custom_fields'	 => true
	);

	$args = wp_parse_args( $args, $defaults );

	if ( ! class_exists( 'USP_Blocks' ) )
		require_once USP_PATH . 'deprecated/class-usp-blocks.php';

	$content = '<div id="usp-user-details">';

	$content .= '<div class="usp-user-avatar">';

	$content .= get_avatar( $user_LK, 600 );

	if ( $args['zoom'] ) {

		$avatar = get_user_meta( $user_LK, 'usp_avatar', 1 );

		if ( $avatar ) {
			if ( is_numeric( $avatar ) ) {
				$url_avatar = get_avatar_url( $user_LK, ['size' => 1000 ] );
			} else {
				$url_avatar = $avatar;
			}
			$content .= '<a title="' . __( 'Zoom avatar', 'usp' ) . '" data-zoom="' . $url_avatar . '" onclick="usp_zoom_avatar(this);return false;" class="usp-avatar-zoom" href="#"><i class="uspi fa-search-plus"></i></a>';
		}
	}

	$content .= '</div>';

	if ( $args['description'] ) {

		$desc = get_the_author_meta( 'description', $user_LK );
		if ( $desc )
			$content .= '<div class="ballun-status">'
				. '<div class="status-user-usp">' . nl2br( wp_strip_all_tags( $desc ) ) . '</div>'
				. '</div>';
	}

	if ( $args['custom_fields'] ) {

		if ( $usp_blocks && (isset( $usp_blocks['details'] ) || isset( $usp_blocks['content'] )) ) {

			$details	 = isset( $usp_blocks['details'] ) ? $usp_blocks['details'] : array();
			$old_output	 = isset( $usp_blocks['content'] ) ? $usp_blocks['content'] : array();

			$details = array_merge( $details, $old_output );

			foreach ( $details as $a => $detail ) {
				if ( ! isset( $details[$a]['args']['order'] ) )
					$details[$a]['args']['order'] = 10;
			}

			for ( $a = 0; $a < count( $details ); $a ++ ) {

				$min		 = $details[$a];
				$newArray	 = $details;

				for ( $n = $a; $n < count( $newArray ); $n ++ ) {

					if ( $newArray[$n]['args']['order'] < $min['args']['order'] ) {
						$details[$n] = $min;
						$min		 = $newArray[$n];
						$details[$a] = $min;
					}
				}
			}

			foreach ( $details as $block ) {
				$USP_Blocks = new USP_Blocks( $block );
				$content .= $USP_Blocks->get_block( $user_LK );
			}
		}
	}

	$content .= '</div>';

	return $content;
}
