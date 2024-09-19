<?php

add_filter( 'usp_field_options', 'usp_edit_field_options', 10, 3 );
function usp_edit_field_options( $options, $field, $manager_id ) {

	$types = [ 'range', 'runner' ];

	if ( in_array( $field->type, $types ) ) {

		foreach ( $options as $k => $option ) {

			if ( $option['slug'] == 'required' ) {
				unset( $options[ $k ] );
			}
		}
	}

	return $options;
}

usp_ajax_action( 'usp_manager_get_new_field', false );
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

usp_ajax_action( 'usp_manager_get_custom_field_options', false );
function usp_manager_get_custom_field_options() {

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

usp_ajax_action( 'usp_manager_get_new_area', false );
function usp_manager_get_new_area() {

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

usp_ajax_action( 'usp_manager_get_new_group', false );
function usp_manager_get_new_group() {

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

usp_ajax_action( 'usp_manager_update_fields_by_ajax', false );
function usp_manager_update_fields_by_ajax() {

	if ( ! current_user_can( 'manage_options' ) ) {
		return [
			'error' => __( 'Error', 'userspace' )
		];
	}

	return usp_manager_update_data_fields();
}

add_action( 'admin_init', 'usp_manager_update_fields_by_post', 10 );
function usp_manager_update_fields_by_post() {

	if ( ! current_user_can( 'manage_options' ) ) {
		return false;
	}

	if ( ! isset( $_POST['usp_manager_update_fields_by_post'], $_POST['_wpnonce'], $_POST['_wp_http_referer'] ) ) {
		return false;
	}

	if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'usp-update-custom-fields' ) ) {
		return false;
	}

	usp_manager_update_data_fields();

	wp_safe_redirect( $_POST['_wp_http_referer'] );
	exit;
}

function usp_manager_update_data_fields() {

	global $wpdb;

	if ( empty( $_POST['manager_id'] ) || empty( $_POST['option_name'] ) || empty( $_POST['fields'] ) ) {
		return [
			'error' => __( 'Error', 'userspace' )
		];
	}

	$copy        = ! empty( $_POST['copy'] ) ? sanitize_text_field( wp_unslash( $_POST['copy'] ) ) : '';
	$manager_id  = sanitize_text_field( wp_unslash( $_POST['manager_id'] ) );
	$option_name = sanitize_text_field( wp_unslash( $_POST['option_name'] ) );

	$fieldsData = usp_recursive_map( 'sanitize_text_field', wp_unslash( $_POST['fields'] ) );
	$structure  = ! empty( $_POST['structure'] ) ? usp_recursive_map( 'sanitize_text_field', wp_unslash( $_POST['structure'] ) ) : false;

	$fields    = [];
	$keyFields = [];
	$changeIds = [];
	$isset_new = false;
	foreach ( $fieldsData as $field_id => $field ) {

		if ( ! $field['title'] ) {
			continue;
		}

		if ( isset( $field['values'] ) ) {
			// remove empty values from the values array
			$values = [];
			foreach ( $field['values'] as $k => $v ) {
				if ( $v == '' ) {
					continue;
				}
				$values[ $k ] = $v;
			}
			$field['values'] = $values;
		}

		if ( stristr( $field_id, 'newField' ) !== false ) {

			$isset_new = true;

			$old_id = $field_id;

			if ( ! $field['id'] ) {

				$field_id = str_replace( [
					'-',
					' '
				], '_', usp_sanitize_string( $field['title'] ) . '-' . uniqid() );
			} else {
				$field_id = $field['id'];
			}

			$changeIds[ $old_id ] = $field_id;
		}

		$field['slug'] = $field_id;

		$keyFields[ $field_id ] = 1;

		unset( $field['id'] );

		$fields[] = $field;
	}

	if ( $structure ) {

		$strArray = [];
		$area_id  = - 1;
		$group_id = 0;
		foreach ( $structure as $value ) {

			if ( is_array( $value ) ) {

				if ( isset( $value['group_id'] ) ) {
					$group_id = $value['group_id'];

					if ( isset( $_POST['structure-groups'][ $group_id ] ) ) {
						$strArray[ $group_id ] = usp_recursive_map( 'sanitize_text_field', wp_unslash( $_POST['structure-groups'][ $group_id ] ) );
					} else {
						$strArray[ $group_id ] = [];
					}

				} else if ( isset( $value['field_id'] ) ) {
					$strArray[ $group_id ]['areas'][ $area_id ]['fields'][] = $value['field_id'];
				}
			} else {
				$area_id ++;
				if ( isset( $_POST['structure-areas'][ $area_id ]['width'] ) ) {
					$strArray[ $group_id ]['areas'][ $area_id ]['width'] = intval( $_POST['structure-areas'][ $area_id ]['width'] );
				} else {
					$strArray[ $group_id ]['areas'][ $area_id ]['width'] = 0;
				}

			}
		}

		$endStructure = [];

		foreach ( $strArray as $group_id => $group ) {

			if ( isset( $group['id'] ) && $group_id != $group['id'] ) {
				$group_id = $group['id'];
			}

			$endStructure[ $group_id ]          = $group;
			$endStructure[ $group_id ]['areas'] = [];

			foreach ( $group['areas'] as $area ) {

				$fieldsArea = [];

				if ( ! empty( $area['fields'] ) ) {

					foreach ( $area['fields'] as $k => $field_id ) {

						if ( isset( $changeIds[ $field_id ] ) ) {
							$field_id = $changeIds[ $field_id ];
						}

						if ( ! isset( $keyFields[ $field_id ] ) ) {
							unset( $area['fields'][ $k ] );
							continue;
						}

						$fieldsArea[] = $field_id;
					}

				}

				$endStructure[ $group_id ]['areas'][] = [
					'width'  => round( $area['width'], 0 ),
					'fields' => $fieldsArea
				];
			}
		}

		$structure = $endStructure;
	}

	$fields = apply_filters( 'usp_pre_update_manager_fields', $fields, $manager_id );

	update_site_option( $option_name, $fields );

	$args = [
		'success' => __( 'Settings saved!', 'userspace' )
	];

	if ( $structure ) {
		update_site_option( 'usp_fields_' . $manager_id . '_structure', $structure );
	} else {
		delete_site_option( 'usp_fields_' . $manager_id . '_structure' );
	}

	if ( ! empty( $_POST['deleted_fields'] ) && ! empty( $_POST['delete_table_data'] ) ) {

		$delete_table_data = usp_recursive_map( 'sanitize_text_field', wp_unslash( $_POST['delete_table_data'] ) );

		foreach ( $delete_table_data as $table_name => $colname ) {

			$fields_to_delete = usp_recursive_map( 'sanitize_text_field', wp_unslash( $_POST['deleted_fields'] ) );

			$wpdb->query( "DELETE FROM $table_name WHERE $colname IN ('" . implode( "','", $fields_to_delete ) . "')" );
		}

		$args['reload'] = true;

	}

	if ( $copy ) {

		update_site_option( 'usp_fields_' . $copy, $fields );

		if ( $structure ) {
			update_site_option( 'usp_fields_' . $copy . '_structure', $structure );
		}

		do_action( 'usp_fields_copy', $fields, $manager_id, $copy );

		$args['reload'] = true;
	}

	if ( $isset_new ) {
		$args['reload'] = true;
	}

	do_action( 'usp_fields_update', $fields, $manager_id );

	return $args;
}
