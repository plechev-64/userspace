<?php

usp_ajax_action( 'usp_manager_get_new_field', false );
usp_ajax_action( 'usp_manager_get_custom_field_options', false );
usp_ajax_action( 'usp_manager_get_new_area', false );
usp_ajax_action( 'usp_manager_get_new_group', false );
usp_ajax_action( 'usp_manager_update_fields_by_ajax', false );
function usp_manager_get_new_field() {

	if ( ! current_user_can( 'manage_options' ) ) {
		return [
			'error' => __( 'Error', 'userspace' )
		];
	}

	if ( empty( $_POST['props'] ) ) {
		return [
			'error' => __( 'Error', 'userspace' )
		];
	}

	$managerProps = usp_recursive_map( 'sanitize_text_field', wp_unslash( $_POST['props'] ) );

	$Manager = new FieldsManager( $managerProps['manager_id'], $managerProps );

	$field_id = 'newField-' . uniqid();

	$Manager->add_field( [
		'slug' => $field_id,
		'type' => $Manager->types[0],
		'_new' => true
	] );

	return [
		'content' => $Manager->get_field_manager( $field_id )
	];
}

function usp_manager_get_custom_field_options(): array {

	if ( ! current_user_can( 'manage_options' ) ) {
		return [
			'error' => __( 'Error', 'userspace' )
		];
	}

	if ( empty( $_POST['manager'] ) || empty( $_POST['newType'] ) || empty( $_POST['fieldId'] ) ) {
		return [
			'error' => __( 'Error', 'userspace' )
		];
	}

	$new_type = sanitize_text_field( wp_unslash( $_POST['newType'] ) );
	$field_id = sanitize_text_field( wp_unslash( $_POST['fieldId'] ) );

	$managerProps = usp_recursive_map( 'sanitize_text_field', wp_unslash( $_POST['manager'] ) );

	$Manager = new FieldsManager( $managerProps['manager_id'], $managerProps );

	if ( stristr( $field_id, 'newField' ) !== false ) {

		$Manager->add_field( [
			'slug' => $field_id,
			'type' => $new_type,
			'_new' => true
		] );
	} else {

		$Manager->set_field_prop( $field_id, 'type', $new_type );

		$Manager->fields[ $field_id ] = $Manager::setup( ( array ) $Manager->fields[ $field_id ] );
	}

	$content = $Manager->get_field_options_content( $field_id );

	$multiVars = [
		'select',
		'radio',
		'checkbox',
		'multiselect'
	];

	if ( in_array( $new_type, $multiVars ) ) {

		$content .= $Manager->sortable_dynamic_values_script( $field_id );
	}

	return [
		'content' => $content
	];
}

function usp_manager_get_new_area(): array {

	if ( ! current_user_can( 'manage_options' ) ) {
		return [
			'error' => __( 'Error', 'userspace' )
		];
	}

	if ( empty( $_POST['props'] ) ) {
		return [
			'error' => __( 'Error', 'userspace' )
		];
	}

	$managerProps = usp_recursive_map( 'sanitize_text_field', wp_unslash( $_POST['props'] ) );

	$Manager = new FieldsManager( 'any', $managerProps );

	return [
		'content' => $Manager->get_active_area()
	];
}

function usp_manager_get_new_group(): array {

	if ( ! current_user_can( 'manage_options' ) ) {
		return [
			'error' => __( 'Error', 'userspace' )
		];
	}

	if ( empty( $_POST['props'] ) ) {
		return [
			'error' => __( 'Error', 'userspace' )
		];
	}

	$managerProps = usp_recursive_map( 'sanitize_text_field', wp_unslash( $_POST['props'] ) );

	$Manager = new FieldsManager( 'any', $managerProps );

	return [
		'content' => $Manager->get_group_areas()
	];
}

function usp_manager_update_fields_by_ajax(): array {

	if ( ! current_user_can( 'manage_options' ) ) {
		return [
			'error' => __( 'Error', 'userspace' )
		];
	}

	return usp_manager_update_data_fields();
}
