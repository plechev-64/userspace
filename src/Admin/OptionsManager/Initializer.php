<?php

namespace USP\Admin\OptionsManager;

class Initializer {
	public function init(): void {
		if ( is_admin() || isset( $_REQUEST['rest_route'] ) ) {
			$this->usp_options_manager_scripts();
		} else {
			add_action( 'usp_enqueue_scripts', [$this, 'usp_options_manager_scripts'], 10 );
		}

		// after save options clear temp db
		add_action( 'usp_update_options', [$this, 'usp_delete_temp_default_avatar_cover'], 10 );
		add_filter( 'usp_options', [$this, 'usp_add_cover_options'], 10 );
	}

	public function usp_options_manager_scripts() {
		wp_enqueue_style( 'usp-options-manager', USP_URL . 'admin/assets/options-manager/usp-options-manager.css' );
		wp_enqueue_script( 'usp-options-manager', USP_URL . 'admin/assets/options-manager/usp-options-manager.js' );
	}

	function usp_delete_temp_default_avatar_cover() {

		if ( isset( $_POST['usp_global_options']['usp_default_avatar'] ) ) {
			usp_delete_temp_media( intval( $_POST['usp_global_options']['usp_default_avatar'] ) );
		}

		if ( isset( $_POST['usp_global_options']['usp_default_cover'] ) ) {
			usp_delete_temp_media( intval( $_POST['usp_global_options']['usp_default_cover'] ) );
		}
	}

	function usp_add_cover_options( $options ) {

		$options->box( 'primary' )->group( 'design' )->add_options( [
			[
				'type'        => 'uploader',
				'temp_media'  => 1,
				'max_size'    => 5120,
				'multiple'    => 0,
				'image_thumb' => 'large',
				'crop'        => [ 'ratio' => 0 ],
				'filetitle'   => 'usp-default-cover',
				'filename'    => 'usp-default-cover',
				'slug'        => 'usp_default_cover',
				'title'       => __( 'Default cover', 'userspace' ),
			],
			[
				'type'       => 'runner',
				'value_min'  => 0,
				'value_max'  => 5120,
				'value_step' => 256,
				'default'    => 1024,
				'slug'       => 'usp_cover_weight',
				'title'      => __( 'Max weight of cover', 'userspace' ) . ', Kb',
				'notice'     => __( 'Set the image upload limit in kb, by default', 'userspace' ) . ' 1024Kb' .
				                '. ' . __( 'If 0 is specified, download is disallowed.', 'userspace' )
			]
		] );

		return $options;
	}
}