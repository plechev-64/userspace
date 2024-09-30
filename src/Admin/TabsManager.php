<?php

namespace USP\Admin;

use USP\Core\Module\FieldsManager\FieldsManager;

class TabsManager extends FieldsManager {
	public function __construct( string $areaType ) {

		parent::__construct( $areaType, [
			'switch_type'   => 0,
			'switch_id'     => 0,
			'types'         => [
				'custom'
			],
			'field_options' => [
				[
					'slug'        => 'icon',
					'default'     => 'fa-check-square',
					'placeholder' => 'fa-check-square',
					'class'       => 'usp-iconpicker',
					'type'        => 'text',
					'title'       => __( 'Icon class of usp-awesome', 'userspace' )
				],
				[
					'type'   => 'radio',
					'slug'   => 'hidden',
					'title'  => __( 'Hidden tab', 'userspace' ),
					'notice' => __( 'The tab will be available only by link', 'userspace' ),
					'values' => [
						__( 'No', 'userspace' ),
						__( 'Yes', 'userspace' )
					]
				],
				[
					'type'        => 'text',
					'slug'        => 'icon',
					'class'       => 'usp-iconpicker',
					'title'       => __( 'Icon class', 'userspace' ),
					'placeholder' => __( 'Example: fa-user', 'userspace' )
				],
				[
					'type'   => 'select',
					'slug'   => 'public',
					'title'  => __( 'Tab privacy', 'userspace' ),
					'values' => [
						__( 'Private', 'userspace' ),
						__( 'Public', 'userspace' )
					]
				],
				[
					'type'   => 'checkbox',
					'slug'   => 'supports',
					'title'  => __( 'Support of the functions', 'userspace' ),
					'values' => [
						'ajax'   => __( 'Ajax-loading', 'userspace' ),
						'cache'  => __( 'Caching', 'userspace' ),
						'dialog' => __( 'Dialog box', 'userspace' )
					]
				],
				[
					'type'    => 'editor',
					'tinymce' => true,
					'slug'    => 'content',
					'title'   => __( 'Content tab', 'userspace' ),
					'notice'  => __( 'Supported shortcodes and HTML-code', 'userspace' )
				]
			]
		] );

		$this->setup_tabs();

		add_filter( 'usp_field_options', [ $this, 'edit_tab_options' ], 10, 2 );
	}

	public function form_navi(): string {

		$areas = [
			'area-menu'     => __( '"Menu" area', 'userspace' ),
			'area-actions'  => __( '"Actions" area', 'userspace' ),
			'area-counters' => __( '"Counters" area', 'userspace' )
		];

		$content = '<div class="usp-custom-fields-navi">';

		$content .= '<ul class="usp-types-list">';

		foreach ( $areas as $type => $name ) {

			$class = ( $this->manager_id == $type ) ? 'current-item' : '';

			$content .= '<li class="usps__inline ' . $class . '">'
			            . '<a class="usps__inline usps__ai-center" href="' . admin_url( 'admin.php?page=usp-tabs-manager&area-type=' . $type ) . '">' . $name . '</a>'
			            . '</li>';
		}

		$content .= '</ul>';

		$content .= '</div>';

		return $content;
	}

	public function is_default_tab( $slug ): bool {
		$tab = USP()->tabs()->tab( $slug );
		if ( ! $tab ) {
			return false;
		}

		return ! $tab->custom_tab;
	}

	public function setup_tabs(): void {

		$defaultTabs = $this->get_default_tabs();

		if ( $this->fields ) {

			foreach ( $this->fields as $k => $tab ) {

				if ( $this->is_default_tab( $tab->id ) ) {
					$tab->set_prop( 'must_delete', false );
				} else {
					if ( isset( $tab->{'default-tab'} ) ) {
						unset( $this->fields[ $k ] );
					}
				}
			}

			if ( $defaultTabs ) {
				foreach ( $defaultTabs as $tab ) {
					if ( $this->is_active_field( $tab['slug'] ) ) {
						continue;
					}
					$this->add_field( $tab );
				}
			}
		} else if ( $defaultTabs ) {

			foreach ( $defaultTabs as $tab ) {
				$this->add_field( $tab );
			}
		}
	}

	public function get_default_tabs(): array {

		if ( ! USP()->tabs()->get_tabs() ) {
			return [];
		}

		$fields = [];

		foreach ( USP()->tabs()->get_tabs() as $tab_id => $tab ) {

			if ( $tab->custom_tab ) {
				continue;
			}

			if ( 'area-' . $tab->output != $this->manager_id ) {
				continue;
			}

			$fields[] = [
				'type-edit'   => false,
				'slug'        => $tab_id,
				'delete'      => false,
				'default-tab' => true,
				'type'        => 'custom',
				'must_delete' => false,
				'title'       => $tab->name,
				'icon'        => $tab->icon
			];
		}

		return $fields;
	}

	public function edit_tab_options( $options, $field ): array {

		if ( ! $field->slug ) {
			return $options;
		}

		if ( $this->is_default_tab( $field->slug ) ) {

			unset( $options['public'] );
			unset( $options['supports'] );
			unset( $options['content'] );
			unset( $options['slug'] );

			$options['icon']['placeholder'] = USP()->tabs()->tab( $field->slug )->icon;

			$options['default-tab'] = [
				'type'  => 'hidden',
				'slug'  => 'default-tab',
				'value' => 1
			];
		} else {
			$options['custom-tab'] = [
				'type'  => 'hidden',
				'slug'  => 'custom-tab',
				'value' => 1
			];
		}

		return $options;
	}

}
