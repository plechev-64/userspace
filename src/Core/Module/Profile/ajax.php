<?php

// Updating the user profile
usp_ajax_action( 'usp_user_update_profile' );
function usp_user_update_profile(): array {
	if ( ! isset( $_POST['submit_user_profile'] ) ) {
		return [
			'error' => __( 'Something has been wrong', 'userspace' ),
		];
	}

	USP()->user()->profile_fields()->update_fields();

	return [
		'notice' => [
			'text'       => __( 'Your profile has been updated', 'userspace' ),
			'type'       => 'success',
			'time_close' => 10000,
		],
	];
}
