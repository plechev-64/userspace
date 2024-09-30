<?php

use USP\Core\Module\ContentManager\ContentManager;
use USP\Core\Module\ContentManager\TableColsManager;
use USP\Core\Module\FieldsManager\FieldsManager;

require_once "admin-menu.php";

add_action( 'current_screen', 'usp_admin_init' );
function usp_admin_init( $current_screen ) {
	if ( preg_match( '/(userspace_page|manage-userspace|profile|user-edit)/', $current_screen->base ) ) {
		usp_admin_resources();
	}
}

add_filter( 'display_post_states', 'usp_mark_own_page', 10, 2 );
function usp_mark_own_page( $post_states, $post ) {

	if ( 'page' === $post->post_type ) {

		$plugin_pages = get_site_option( 'usp_plugin_pages' );

		if ( ! $plugin_pages ) {
			return $post_states;
		}

		if ( in_array( $post->ID, $plugin_pages ) ) {
			$post_states[] = __( 'The page of plugin UserSpace', 'userspace' );
		}
	}

	return $post_states;
}

// set admin area root inline css colors
add_filter( 'admin_head', 'usp_admin_css_variable' );
function usp_admin_css_variable() {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<style>' . usp_get_root_colors() . '</style>';
}

// get standard header of admin
function usp_get_admin_header( $title = false, $subtitle = false ) {
	$out = '<div class="usp-admin-head usps usps__jc-between">';

	$out .= '<div class="usp-admin-head__left usps usps__column">';

	$out .= '<div class="usp-admin-head__top usps">';
	$out .= '<div class="usp-admin-head__logo usps usps__ai-center"><span class="dashicons dashicons-palmtree"></span><span class="usp-admin-head__name">UserSpace</span></div>';
	$out .= '<div class="usp-admin-head__version">v.' . USP_VERSION . '</div>';
	$out .= '</div>';

	$out .= '<div class="usp-admin-head__bottom usps usps__column">';
	$out .= '<h2 class="usp-admin-head__title">' . $title . '</h2>';
	$out .= '<div class="usp-admin-head__subtitle usps">' . $subtitle . '</div>';
	$out .= '</div>';

	$out .= '</div>'; // end .usp-admin-head__left

	$out .= '<div class="usp-admin-head__right usps usps__grow usps__jc-end">';
	$out .= '<div class="usps usps__ai-center"><span class="dashicons dashicons-media-document"></span><a href="#" target="_blank">' . __( 'Documentation', 'userspace' ) . '</a></div>';
	$out .= '<div class="usps usps__ai-center"><span class="dashicons dashicons-editor-help"></span><a href="#" target="_blank">' . __( 'Support', 'userspace' ) . '</a></div>';
	$out .= '</div>'; // end .usp-admin-head__right

	$out .= '</div>'; // end .usp-admin-head

	return $out;
}

// get standard content of admin
function usp_get_admin_content( $content, $no_sidebar = false ) {
	$class = ( $no_sidebar ) ? 'usp-admin__fullwidth' : '';

	$out = '<div class="usp-admin__box usps usps__nowrap usps__jc-between">';
	$out .= '<div class="usp-admin__settings usps__grow ' . $class . '">' . $content . '</div>';
	if ( ! $no_sidebar ) {
		/**
		 * On the plugin settings pages, adds custom html to the sidebar.
		 *
		 * @param string    Added custom html.
		 *
		 * @since 1.0.0
		 *
		 */
		$out .= '<div class="usp-admin__sidebar usps usps__column usps__ai-end usps__grow">' . apply_filters( 'usp_admin_sidebar', '' ) . '</div>';
	}

	$out .= '</div>';

	return $out;
}

add_filter( 'usp_admin_sidebar', 'usp_admin_sidebar_about_notice', 10 );
function usp_admin_sidebar_about_notice( $content ) {
	// get plugin description header
	$text = get_file_data( USP_PATH . 'userspace.php', [ 'description' => 'Description' ] );

	$content .= usp_get_notice( [
		'text'   => 'UserSpace - ' . $text['description'],
		'type'   => 'simple',
		'icon'   => false,
		'cookie' => 'usp_userspace_about',
	] );

	return $content;
}

add_filter( 'usp_admin_sidebar', 'usp_admin_sidebar_find_addons_notice', 11 );
function usp_admin_sidebar_find_addons_notice( $content ) {
	// translators: %s is a link of WordPress repository
	$text = sprintf( __( 'Plugins that extend UserSpace can be found in the WordPress  %srepository%s.', 'userspace' ), '<a href="https://wordpress.org/plugins/tags/userspace/" target="_blank">', '</a>' );

	$content .= usp_get_notice( [ 'text' => $text, 'type' => 'simple', 'icon' => false ] );

	return $content;
}

add_filter( 'usp_admin_sidebar', 'usp_admin_sidebar_rate_me_notice', 12 );
function usp_admin_sidebar_rate_me_notice( $content ) {
	// translators: %s is a link of WordPress repository
	$text = sprintf( __( 'If you liked plugin %sUserSpace%s, please vote for it in repository %s★★★★★%s. Thank you so much!', 'userspace' ), '<strong>', '</strong>', '<a href="" target="_blank">', '</a>' );

	$content .= usp_get_notice( [
		'text'   => $text,
		'type'   => 'simple',
		'icon'   => false,
		'cookie' => 'usp_repo_votes',
	] );

	return $content;
}

add_action( 'admin_init', 'usp_manager_update_fields_by_post', 10 );
function usp_manager_update_fields_by_post(): void {

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( ! isset( $_POST['usp_manager_update_fields_by_post'], $_POST['_wpnonce'], $_POST['_wp_http_referer'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'usp-update-custom-fields' ) ) {
		return;
	}

	usp_manager_update_data_fields();

	wp_safe_redirect( $_POST['_wp_http_referer'] );
	exit;
}

function usp_manager_update_data_fields(): array {

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
