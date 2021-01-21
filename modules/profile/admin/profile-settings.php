<?php

add_filter( 'usp_options', 'usp_profile_options' );
function usp_profile_options( $options ) {

	$options->add_box( 'profile', array(
		'title'	 => __( 'Settings profile', 'userspace' ),
		'icon'	 => 'fa-user'
	) )->add_group( 'general' )->add_options( array(
		array(
			'type'	 => 'select',
			'slug'	 => 'delete_user_account',
			'title'	 => __( 'Allow users to delete their account?', 'userspace' ),
			'values' => array( __( 'No', 'userspace' ), __( 'Yes', 'userspace' ) )
		)
	) );

	return $options;
}
