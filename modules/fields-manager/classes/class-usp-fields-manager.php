<?php

class USP_Fields_Manager extends USP_Fields {

	public $manager_id = false;
	//public $fields = array();
	public $option_name = '';
	public $structure_edit = 0;
	public $template_fields = 0;
	public $default_fields = [];
	public $default_is_null = 0;
	public $sortable = 1;
	public $empty_field = 1;
	public $create_field = 1;
	public $switch_id = 0;
	public $switch_type = 1;
	public $fields_delete = 1;
	public $field_options = [];
	public $new_field_options = [];
	public $new_field_type = 0;
	public $default_box = 1;
	public $meta_delete = 0;
	public $current_item = 0;
	public $group_id = 0;
	public $onsubmit = 'usp_manager_update_fields';
	public $types = [
		'text',
		'textarea',
		'select',
		'multiselect',
		'checkbox',
		'radio',
		'email',
		'tel',
		'number',
		'date',
		'time',
		'url',
		'agree',
		'file',
		'dynamic',
		'runner',
		'range',
		'editor',
		'uploader'
	];

	function __construct( $manager_id, $args = false ) {

		usp_dialog_scripts();

		usp_iconpicker();

		$this->manager_id = $manager_id;

		$this->init_properties( $args );

		if ( $this->sortable ) {
			usp_sortable_scripts();
		}

		//if ( $this->structure_edit )
		usp_resizable_scripts();

		if ( ! $this->option_name ) {
			$this->option_name = 'usp_fields_' . $this->manager_id;
		}

		$fields = apply_filters( 'usp_custom_fields', $this->get_active_fields(), $this->manager_id );

		parent::__construct( $fields, $this->get_structure() );

		$this->setup_active_fields();

		if ( $this->template_fields ) {
			$this->setup_template_fields();
		}
	}

	function init_properties( $args ) {

		$properties = get_class_vars( get_class( $this ) );

		foreach ( $properties as $name => $val ) {
			if ( isset( $args[ $name ] ) ) {
				$this->$name = is_bool( $args[ $name ] ) ? ( boolean ) $args[ $name ] : $args[ $name ];
			}
		}
	}

	function setup_template_fields( $fields = false ) {

		if ( ! $fields ) {
			$fields = $this->get_template_fields();
		}

		if ( ! $fields || ! is_array( $fields ) ) {
			return false;
		}

		$template_fields = [];

		foreach ( $fields as $field ) {

			if ( ! $field ) {
				continue;
			}

			$template_fields[ $field['slug'] ] = $this::setup( $field );
		}

		if ( $template_fields ) {
			$this->template_fields = $template_fields;
		}
	}

	function get_template_fields() {
		return apply_filters( 'usp_template_fields_manager', $this->template_fields, $this->manager_id );
	}

	function setup_default_fields( $fields = false ) {

		if ( ! $fields ) {
			$fields = $this->get_default_fields();
		}

		if ( ! $fields ) {
			return false;
		}

		$default_fields = [];

		foreach ( $fields as $field ) {

			if ( ! $field ) {
				continue;
			}

			$default_fields[ $field['slug'] ] = $this::setup( $field );

			if ( ! $this->default_box && ! $this->is_active_field( $field['slug'] ) ) {
				$this->add_field( $field );
			}
		}

		if ( $default_fields ) {
			$this->default_fields = $default_fields;
		}

		if ( ! $this->fields && $this->default_is_null ) {

			$this->fields = $this->default_fields;

			$this->setup_structure( true );
		}
	}

	function setup_active_fields() {

		$fields = $this->get_default_fields();

		if ( ! $fields ) {
			return false;
		}

		foreach ( $fields as $field ) {

			if ( ! $field || ! $this->is_active_field( $field['slug'] ) ) {
				continue;
			}


			if ( ! isset( $field['options'] ) ) {
				continue;
			}

			$activeField = $this->get_field( $field['slug'] );

			$activeField->set_prop( 'options', $field['options'] );
		}
	}

	function setup_fields( $fields ) {
		if ( is_array( $fields ) ) {
			parent::__construct( $fields );
		}
	}

	function get_active_fields() {

		/* $name_option = 'usp_fields_'.$this->manager_id;

		  if(!$fields = get_site_option($name_option)){

		  switch($this->manager_id){
		  case 'post': $fields = get_site_option('usp_fields_post_1'); break;
		  case 'orderform': $fields = get_site_option('usp_cart_fields'); break;
		  case 'profile': $fields = get_site_option('usp_profile_fields'); break;
		  }

		  } */

		return apply_filters( $this->option_name . '_in_manager', get_site_option( $this->option_name ) );
	}

	function get_structure() {
		if ( ! $this->structure_edit ) {
			return false;
		}

		return get_site_option( 'usp_fields_' . $this->manager_id . '_structure' );
	}

	function get_field( $field_id, $serviceType = false ) {
		if ( ! $serviceType ) {
			return isset( $this->fields[ $field_id ] ) ? $this->fields[ $field_id ] : false;
		} else if ( $serviceType == 'default' ) {
			return $this->default_fields[ $field_id ];
		} else if ( $serviceType == 'template' ) {
			return $this->template_fields[ $field_id ];
		}
	}

	function add_field( $args, $serviceType = false ) {
		if ( $serviceType ) {
			$this->default_fields[ $args['slug'] ] = $this::setup( $args );
		} else {
			$this->fields[ $args['slug'] ] = $this::setup( $args );
		}
	}

	function set_field_prop( $field_id, $propName, $propValue, $serviceType = false ) {

		$field = $this->get_field( $field_id, $serviceType );

		$field->$propName = $propValue;

		if ( $serviceType ) {
			$this->default_fields[ $field_id ] = $field;
		} else {
			$this->fields[ $field_id ] = $field;
		}
	}

	function isset_field_prop( $field_id, $propName, $serviceType = false ) {

		$field = $this->get_field( $field_id, $serviceType );

		if ( ! $field ) {
			return false;
		}

		return isset( $field->$propName );
	}

	function get_field_prop( $field_id, $propName, $serviceType = false ) {

		if ( ! $this->isset_field_prop( $field_id, $propName, $serviceType ) ) {
			return false;
		}

		$field = $this->get_field( $field_id, $serviceType );

		return $field->$propName;
	}

	function get_manager() {

		$content = '<div class="usp-frame ' . ( $this->structure_edit ? 'usp-frame-edit' : 'usp-frame-easy' ) . '">';

		if ( $this->meta_delete ) {
			$content .= '<span style="display:none" id="usp-manager-confirm-delete">' . __( 'To delete a data adding this field?', 'userspace' ) . '</span>';
		}

		if ( $this->template_fields ) {
			$content .= '<div class="usp-frame__box service-box">';
			$content .= '<div class="usp-frame__title">' . __( 'Templates', 'userspace' ) . '</div>';
			$content .= $this->get_service_box();
			$content .= '</div>';
		}

		if ( $this->default_fields && $this->default_box ) {
			$content .= '<div class="usp-frame__box usp-frame__box-default">';
			$content .= '<div class="usp-frame__title">' . __( 'Inactive fields', 'userspace' ) . '</div>';
			$content .= $this->get_default_box();
			$content .= '</div>';
		}

		$content .= '<div class="usp-frame__box usp-frame__box-fields">';
		$content .= '<div class="usp-frame__title">' . __( 'Active fields', 'userspace' ) . '</div>';
		$content .= '<form method="post" action="" class="usp-frame__form" ' . ( $this->onsubmit ? 'onsubmit="' . $this->onsubmit . '();return false;"' : '' ) . '>';

		$content .= $this->get_manager_options_form();

		$content .= '<div class="usp-frame__panel usp-preloader-parent">';

		foreach ( $this->structure as $group_id => $group ) {
			$content .= $this->get_group_areas( $group );
		}

		$content .= '</div>';

		$content .= $this->get_submit_box();
		$content .= '<input type="hidden" name="manager_id" value="' . $this->manager_id . '">';
		$content .= '<input type="hidden" name="option_name" value="' . $this->option_name . '">';

		if ( ! $this->onsubmit ) {
			$content .= wp_nonce_field( 'usp-update-custom-fields', '_wpnonce', true, false );
			$content .= '<input type="hidden" name="usp_manager_update_fields_by_post" value="1">';
		}

		$content .= '</form>';
		$content .= '</div>';

		$content .= '</div>';

		if ( $this->sortable ) {
			$content .= $this->sortable_fields_script();
		}

		$content .= $this->resizable_areas_script();

		$content .= $this->sortable_dynamic_values_script();

		$props = get_object_vars( $this );

		unset( $props['fields'] );
		unset( $props['default_fields'] );

		$content .= '<script>jQuery(window).on("load", function() {usp_init_manager_fields(' . json_encode( $props ) . ');});</script>';

		return $content;
	}

	function get_manager_options_form() {

		$fields = $this->get_manager_options_form_fields();

		if ( ! $fields ) {
			return false;
		}

		$content = '<div class="usp-frame__options">';
		foreach ( $fields as $field ) {
			$content .= $this::setup( $field )->get_field_html();
		}
		$content .= '</div>';

		return $content;
	}

	function get_manager_options_form_fields() {
		return [];
	}

	function get_group_areas( $group = [] ) {

		$group = wp_parse_args( $group, [
			'title' => '',
			'id'    => 'section-' . uniqid(),
			'type'  => 0,
			'areas' => [
				[
					'fields' => []
				]
			]
		] );

		$content = '<div id="usp-frame__section-' . $this->group_id . '" class="usp-frame__section usps__relative">';

		if ( $this->structure_edit ) {

			$this->group_id = $group['id'];

			$content .= '<input type="hidden" name="structure[][group_id]" value="' . $this->group_id . '">';

			$content .= '<div class="usp-frame__section-header">';

			$content .= '<div class="usp-frame__section-settings usps usps__jc-between usps__ai-center">';

			$content .= '<div class="usp-frame__section-name">';
			$content .= $this::setup( [
				'slug'        => 'group-title',
				'type'        => 'text',
				'input_name'  => 'structure-groups[' . $this->group_id . '][title]',
				'placeholder' => __( 'Name of the section', 'userspace' ),
				'value'       => $group['title']
			] )->get_field_html();
			$content .= '</div>';

			$content .= '<div class="usp-frame__control usps usps__jc-between usps__relative">';

			//if ( count( $this->structure ) > 1 ) {
			$content .= usp_get_button( [
				'size'    => 'medium',
				'type'    => 'clear',
				'title'   => __( 'Delete section', 'userspace' ),
				'icon'    => 'fa-trash',
				'class'   => 'usp-frame__control-bttn usp-frame__section-bttn-delete',
				'onclick' => 'usp_remove_manager_group("' . __( 'Are you sure?', 'userspace' ) . '",this);return false;',
			] );
			//}

			$content .= usp_get_button( [
				'size'    => 'medium',
				'type'    => 'clear',
				'title'   => __( 'Settings of section', 'userspace' ),
				'icon'    => 'fa-horizontal-sliders',
				'class'   => 'usp-frame__control-bttn usp-frame__section-bttn-edit',
				'onclick' => 'usp_switch_view_settings_manager_group(this);return false;',
			] );

			$content .= usp_get_button( [
				'size'    => 'medium',
				'type'    => 'clear',
				'label'   => __( 'Add a group of fields', 'userspace' ),
				'icon'    => 'fa-plus',
				'class'   => 'usp-frame__control-bttn usp-frame__section-bttn-add',
				'onclick' => 'usp_manager_get_new_area(this);return false;',
			] );

			$content .= '</div>';

			$content .= '</div>';

			$fields = [
				'group-id'     => [
					'slug'       => 'group-id',
					'type'       => 'text',
					'input_name' => 'structure-groups[' . $this->group_id . '][id]',
					'title'      => __( 'Section ID', 'userspace' ),
					'required'   => true,
					'value'      => $this->group_id
				],
				'group-notice' => [
					'slug'       => 'group-notice',
					'type'       => 'text',
					'input_name' => 'structure-groups[' . $this->group_id . '][notice]',
					'title'      => __( 'A note of this section', 'userspace' ),
					'value'      => isset( $group['notice'] ) ? $group['notice'] : ''
				]
			];

			$content .= '<div class="usp-frame__section-more-options usps__hidden">';
			foreach ( $fields as $field ) {
				$content .= $this::setup( $field )->get_field_html();
			}
			$content .= '</div>';

			$content .= '</div>';
		}

		$content .= '<div class="usp-frame__section-content usps usps__nowrap usps__jc-center usp-preloader-parent">';

		foreach ( $group['areas'] as $area ) {
			$content .= $this->get_active_area( $area );
		}

		$content .= '</div>';
		$content .= '</div>';

		return $content;
	}

	function get_active_area( $area = [] ) {

		if ( $this->empty_field ) {

			$this->add_field( [
				'slug' => 'newField-' . uniqid(),
				'type' => $this->types[0],
				'_new' => true
			] );
		}

		$widthArea = isset( $area['width'] ) && $area['width'] ? $area['width'] : 100;

		$content = '<div class="usp-frame__group usp-preloader-parent" style="width:' . ( $widthArea ? $widthArea . '%' : 'auto' ) . ';">';

		if ( $this->structure_edit ) {

			$content .= '<div class="usp-frame__group-width usps__hidden">' . $widthArea . '</div>';

			$content .= '<input type="hidden" name="structure[]" value="area">';
			$content .= '<input type="hidden" class="area-width" name="structure-areas[][width]" value="' . $widthArea . '">';
		}

		$content .= '<div class="usp-frame__group-box">';

		if ( $this->structure_edit ) {

			$content .= '<div class="usp-frame__control usps usps__jc-between usps__relative">';

			$content .= usp_get_button( [
				'size'    => 'medium',
				'type'    => 'clear',
				'title'   => __( 'Delete group of fields', 'userspace' ),
				'icon'    => 'fa-trash',
				'class'   => 'usp-frame__group-bttn-delete',
				'onclick' => 'usp_remove_manager_area("' . __( 'Are you sure?', 'userspace' ) . '",this);return false;',
			] );

			if ( $this->sortable ) {
				$content .= usp_get_button( [
					'size'  => 'medium',
					'type'  => 'clear',
					'title' => '',
					'icon'  => 'fa-arrows',
					'class' => 'usp-frame__group-bttn-move',
				] );
			}

			/* if ( $this->create_field ) {
			  $content .= '<a href="#" onclick="usp_manager_get_new_field(this);return false;" title="' . __( 'Add field', 'userspace' ) . '" class="add-field"><i class="uspi fa-plus-square" aria-hidden="true"></i> ' . __( 'Add field', 'userspace' ) . '</a>';
			  } */
			$content .= '</div>';
		}

		$content .= '<div class="usp-frame__group-fields fields-box">';

		if ( $this->fields ) {

			if ( $this->structure_edit ) {

				if ( isset( $area['fields'] ) && $area['fields'] ) {
					foreach ( $area['fields'] as $field_id ) {
						if ( ! $this->is_active_field( $field_id ) ) {
							continue;
						}

						$content .= $this->get_field_manager( $field_id );
					}
				}
			} else {

				foreach ( $this->fields as $field_id => $field ) {
					if ( ! $this->is_active_field( $field_id ) ) {
						continue;
					}

					$content .= $this->get_field_manager( $field_id, false );
				}
			}
		}

		$content .= '</div>';

		$content .= '<div class="submit-box">';

		if ( $this->create_field ) {
			$content .= usp_get_button( [
				'size'      => 'medium',
				'type'      => 'simple',
				'label'     => __( 'Add new field', 'userspace' ),
				'icon'      => 'fa-plus',
				'class'     => [ 'add-field-button' ],
				'fullwidth' => 1,
				'onclick'   => 'usp_manager_get_new_field(this);',
			] );
		}

		$content .= '</div>';

		$content .= '</div>';

		$content .= '</div>';

		return $content;
	}

	function get_submit_box() {

		$content = '<div class="submit-box">';

		if ( $this->structure_edit ) {
			$content .= usp_get_button( [
				'size'      => 'medium',
				'type'      => 'simple',
				'label'     => __( 'Add new section', 'userspace' ),
				'icon'      => 'fa-plus',
				'class'     => [ 'add-field-button' ],
				'fullwidth' => 1,
				'onclick'   => 'usp_manager_get_new_group(this);',
			] );
		}

		$content .= "<input class='button button-primary' type=submit value='" . __( 'Save', 'userspace' ) . "' name='usp_save_custom_fields'>";

		if ( $this->meta_delete ) {
			foreach ( $this->meta_delete as $table_name => $colname ) {
				$content .= "<input type=hidden name=delete_table_data[$table_name] value='$colname'>";
			}

			$content .= "<div id='field-delete-confirm' style='display:none;'>" . __( 'To remove the data added to this field?', 'userspace' ) . "</div>";
		}

		$content .= '</div>';

		return $content;
	}

	function get_default_box() {

		if ( ! $this->default_fields ) {
			return false;
		}

		$content = '<div class="usp-frame__service usp-frame__default fields-box">';

		foreach ( $this->default_fields as $field_id => $field ) {

			if ( $this->is_active_field( $field_id ) ) {
				continue;
			}

			$content .= $this->get_field_manager( $field_id, 'default' );
		}

		$content .= '</div>';

		return $content;
	}

	function get_service_box() {

		if ( ! $this->template_fields ) {
			return false;
		}

		$content = '<div class="usp-frame__service usp-template-fields fields-box">';

		foreach ( $this->template_fields as $field_id => $field ) {
			$content .= $this->get_field_manager( $field_id, 'template' );
		}

		$content .= '</div>';

		return $content;
	}

	function get_default_fields() {

		return apply_filters( 'usp_default_fields_manager', $this->default_fields, $this->manager_id );
	}

	function get_field_manager( $field_id, $serviceType = false ) {

		$field = $this->get_field( $field_id, $serviceType );

		$classes = [ 'usp-frame__field' ];

		if ( $this->is_service_type( $field_id, 'default' ) ) {
			$classes[] = 'default-field';
		} else if ( $this->is_service_type( $field_id, 'template' ) ) {
			$classes[] = 'template-field';
		}

		if ( $this->meta_delete ) {
			$classes[] = 'must-meta-delete';
		}

		$content = '<div id="usp-frame__field-' . $field_id . '" class="' . implode( ' ', $classes ) . '" data-type="' . $field->type . '" data-id="' . $field_id . '">';

		if ( $this->structure_edit ) {
			$content .= '<input type="hidden" name="structure[][field_id]" value="' . $field_id . '">';
		}

		$content .= $this->get_field_header( $field_id, $serviceType );

		$content .= $this->get_field_options_box( $field_id, $serviceType );

		$content .= '</div>';

		return $content;
	}

	function setup_options( $options, $field_id, $serviceType = false ) {

		if ( ! $options ) {
			return $options;
		}

		$field = $this->get_field( $field_id, $serviceType );

		foreach ( $options as $k => $option ) {

			$option_id = $option['slug'];

			if ( ! isset( $option['input_name'] ) ) {
				$options[ $k ]['input_name'] = 'fields[' . $field_id . '][' . $option['slug'] . ']';
			}

			if ( ! isset( $option['value'] ) && isset( $field->$option_id ) ) {
				$options[ $k ]['value'] = $field->$option_id;
			}
		}

		return $options;
	}

	function get_field_header( $field_id, $serviceType = false ) {

		$field = $this->get_field( $field_id, $serviceType );

		$content = '<div class="usp-frame__field-header usps usps__nowrap usps__ai-center">';
		$content .= '<span class="usp-frame__field-icon icon-type-' . $field->type . '"></span>';

		if ( $field->is_new() ) {
			$content .= $this::setup( [
				'slug'        => 'title',
				'type'        => 'text',
				'placeholder' => __( 'Enter a name for the new field', 'userspace' ),
				'input_name'  => 'fields[' . $field_id . '][title]'
			] )->get_field_html();
		} else {
			$content .= $this::setup( [
				'slug'        => 'title',
				'type'        => 'text',
				'placeholder' => __( 'Point a title of this field', 'userspace' ),
				'input_name'  => 'fields[' . $field_id . '][title]',
				'value'       => $field->title
			] )->get_field_html();
			//$content .= '<span class="field-title">'.$field->title.'</span>';
		}

		$buttons = $this->get_control_buttons( $field_id, $field );

		if ( $buttons ) {
			$content .= '<span class="usp-frame__field-control usps usps__nowrap usps__jc-end">';

			foreach ( $buttons as $button ) {
				$content .= usp_get_button( $button );
			}

			$content .= '</span>';
		}

		$content .= '</div>';

		return $content;
	}

	function get_control_buttons( $field_id, $field ) {

		$buttons = [];

		if ( $field->must_delete && $this->fields_delete && ! $this->is_service_type( $field_id, 'default' ) && ! $field->is_new() ) {
			$buttons['delete'] = [
				'icon'    => 'fa-trash',
				'class'   => 'usp-frame__field-bttn-delete',
				'onclick' => 'usp_manager_field_delete("' . $field_id . '", ' . ( $this->meta_delete ? 1 : 0 ) . ', this);return false;',
			];
		}

		$buttons['edit'] = [
			'class'   => 'usp-frame__field-bttn-edit',
			'icon'    => 'fa-horizontal-sliders',
			'onclick' => 'usp_manager_field_switch(this);return false;'
		];

		if ( $this->sortable ) {
			$buttons['sortable'] = [
				'class' => 'usp-frame__field-bttn-move',
				'icon'  => 'fa-arrows'
			];
		}

		$buttons = apply_filters( 'usp_manager_field_controls', $buttons, $field_id, $this->manager_id );

		return $buttons;
	}

	function get_field_options_box( $field_id, $serviceType = false ) {

		$field = $this->get_field( $field_id, $serviceType );

		$content = '<div class="usp-frame__field-settings usps__hidden">';

		if ( ! $field->is_new() ) {
			$content .= '<span class="usp-frame__field-id usps__line-1">' . __( 'ID', 'userspace' ) . ': ' . $field_id . '</span>';
		}

		$content .= $this->get_field_general_options_content( $field_id, $serviceType );

		$content .= $this->get_field_options_content( $field_id, $serviceType );

		$content .= '</div>';

		return $content;
	}

	function get_field_general_options_content( $field_id, $serviceType = false ) {

		$options = $this->get_field_general_options( $field_id, $serviceType );

		if ( ! $options ) {
			return false;
		}

		$content = '<div class="field-primary-options">';

		foreach ( $options as $option ) {
			$content .= $this::setup( $option )->get_field_html();
		}

		$content .= '</div>';

		return $content;
	}

	function get_field_options_content( $field_id, $serviceType = false ) {

		$options = $this->get_field_options( $field_id, $serviceType );

		$content = '<div class="field-secondary-options">';

		foreach ( $options as $option ) {
			$content .= $this::setup( $option )->get_field_html();
		}

		$content .= '</div>';

		return $content;
	}

	function get_field_general_options( $field_id, $serviceType = false ) {

		$field = $this->get_field( $field_id, $serviceType );

		if ( $field->is_new() || $this->switch_id ) {
			$options['id'] = [
				'slug'        => 'id',
				'type'        => 'text',
				'pattern'     => '[a-z0-9-_]+',
				'value'       => $field->is_new() ? '' : $field_id,
				'title'       => __( 'ID', 'userspace' ),
				'notice'      => __( 'Not required, but you can list your own meta_key in this field', 'userspace' ),
				'placeholder' => __( 'Latin letters and numbers', 'userspace' )
			];
		}

		if ( $this->switch_type ) {

			if ( $typeList = $this->get_types_list() ) {

				if ( $this->is_service_type( $field_id ) || ! isset( $typeList[ $field->type ] ) ) {
					// for default fields we set a fixed type
					$options['type'] = [
						'slug'  => 'type',
						'type'  => 'hidden',
						'value' => $field->type
					];
				} else {
					$options['type'] = [
						'slug'         => 'type',
						'type'         => 'select',
						'title'        => __( 'Type of field', 'userspace' ),
						'values'       => $typeList,
						'value_in_key' => false
					];
				}
			}
		} else {

			$options['type'] = [
				'slug'  => 'type',
				'type'  => 'hidden',
				'value' => ( $field->is_new() && $this->new_field_type ) ? $this->new_field_type : $field->type
			];
		}

		$options = apply_filters( 'usp_field_general_options', $options, $field, $this->manager_id );

		return $this->setup_options( $options, $field_id, $serviceType );
	}

	function get_field_options( $field_id, $serviceType = false ) {

		$options = [];

		$field = $this->get_field( $field_id, $serviceType );

		$fieldOptions = $field->get_options();

		if ( $fieldOptions ) {
			foreach ( $fieldOptions as $option ) {
				$options[ $option['slug'] ] = $option;
			}
		}

		if ( $this->field_options ) {

			foreach ( $this->field_options as $option ) {
				$option                     = ( array ) $option;
				$options[ $option['slug'] ] = $option;
			}
		}

		if ( $field->is_new() && $this->new_field_options ) {

			foreach ( $this->new_field_options as $option ) {
				$option                     = ( array ) $option;
				$options[ $option['slug'] ] = $option;
			}
		}

		if ( isset( $field->options ) ) {
			foreach ( $field->options as $option ) {
				$options[ $option['slug'] ] = $option;
			}
		}

		if ( ! $serviceType && $this->is_default_field( $field_id ) ) {
			// for the field in the active zone, add the options that were defined for the default field, if any
			$defaultField = $this->get_field( $field_id, 1 );

			if ( isset( $defaultField->options ) ) {

				foreach ( $defaultField->options as $option ) {
					$options[ $option['slug'] ] = $option;
				}
			}
		}

		$options = apply_filters( 'usp_field_options', $options, $field, $this->manager_id, $this );

		return $this->setup_options( $options, $field_id, false );
	}

	function sortable_fields_script() {
		return '<script>jQuery(window).on("load", function() { usp_init_manager_sortable(); });</script>';
	}

	function resizable_areas_script() {
		return '<script>jQuery(window).on("load", function() { usp_init_manager_areas_resizable(); });</script>';
	}

	function sortable_dynamic_values_script( $field_id = false ) {

		return '<script>
				jQuery(function(){
					jQuery("' . ( $field_id ? "#usp-frame__field-" . $field_id . " " : '' ) . '.usp-field-input .dynamic-values").sortable({
						containment: "parent",
						placeholder: "ui-sortable-placeholder",
						distance: 15,
						stop: function( event, ui ) {

							var items = ui.item.parents(".dynamic-values").find(".dynamic-value");

							items.each(function(f){
								if(items.length == (f+1)){
									jQuery(this).children("a").attr("onclick","usp_add_dynamic_field(this);return false;").children("i").attr("class","usp-bttn__ico usp-bttn__ico-left uspi fa-plus");
								}else{
									jQuery(this).children("a").attr("onclick","usp_remove_dynamic_field(this);return false;").children("i").attr("class","usp-bttn__ico usp-bttn__ico-left uspi fa-minus");
								}
							});

						}
					});
				});
			</script>';
	}

	function is_service_type( $field_id, $serviceType = [ 'default', 'template' ] ) {

		if ( is_array( $serviceType ) ) {

			if ( in_array( 'default', $serviceType ) && isset( $this->default_fields[ $field_id ] ) ) {
				return true;
			} else if ( in_array( 'template', $serviceType ) && isset( $this->template_fields[ $field_id ] ) ) {
				return true;
			}

			return false;
		}

		if ( $serviceType == 'default' ) {
			return isset( $this->default_fields[ $field_id ] );
		} else if ( $serviceType == 'template' ) {
			return isset( $this->template_fields[ $field_id ] );
		}
	}

	function is_active_field( $field_id ) {
		return isset( $this->fields[ $field_id ] );
	}

	function is_default_field( $field_id ) {
		return $this->is_service_type( $field_id, 'default' );
	}

	function get_types_list() {

		$typesList = [];
		foreach ( $this->types as $type ) {
			if ( ! isset( USP()->get_fields()[ $type ] ) ) {
				continue;
			}
			$typesList[ $type ] = USP()->get_fields()[ $type ]['label'];
		}

		return $typesList;
	}

}
