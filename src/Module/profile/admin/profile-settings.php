<?php

add_filter( 'usp_options', 'usp_profile_options' );
function usp_profile_options( $options ) {

	$options->add_box( 'profile', [
		'title' => __( 'Settings profile', 'userspace' ),
		'icon'  => 'fa-user'
	] )->add_group( 'general' )->add_options( [
		[
			'type'    => 'switch',
			'slug'    => 'usp_user_deleting_profile',
			'title'   => __( 'Allow users to delete their account?', 'userspace' ),
			'text'    => [
				'off' => __( 'No', 'userspace' ),
				'on'  => __( 'Yes', 'userspace' )
			],
			'default' => 0,
		]
	] );

	return $options;
}
