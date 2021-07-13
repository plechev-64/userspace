<?php

USP()->use_module( 'fields-manager' );

class USP_Tabs_Manager extends USP_Fields_Manager {
    function __construct( $areaType ) {

        parent::__construct( $areaType, array(
            'switch_type'   => 0,
            'switch_id'     => 0,
            'types'         => array(
                'custom'
            ),
            'field_options' => array(
                array(
                    'slug'        => 'icon',
                    'default'     => 'fa-check-square',
                    'placeholder' => 'fa-check-square',
                    'class'       => 'usp-iconpicker',
                    'type'        => 'text',
                    'title'       => __( 'Icon class of usp-awesome', 'userspace' )
                ),
                array(
                    'type'   => 'radio',
                    'slug'   => 'hidden',
                    'title'  => __( 'Hidden tab', 'userspace' ),
                    'notice' => __( 'The tab will be available only by link', 'userspace' ),
                    'values' => array(
                        __( 'No', 'userspace' ),
                        __( 'Yes', 'userspace' )
                    )
                ),
                array(
                    'type'        => 'text',
                    'slug'        => 'icon',
                    'class'       => 'usp-iconpicker',
                    'title'       => __( 'Icon class', 'userspace' ),
                    'placeholder' => __( 'Example: fa-user', 'userspace' )
                ),
                array(
                    'type'   => 'select',
                    'slug'   => 'public-tab',
                    'title'  => __( 'Tab privacy', 'userspace' ),
                    'values' => array(
                        __( 'Private', 'userspace' ),
                        __( 'Public', 'userspace' )
                    )
                ),
                array(
                    'type'   => 'checkbox',
                    'slug'   => 'supports-tab',
                    'title'  => __( 'Support of the functions', 'userspace' ),
                    'values' => array(
                        'ajax'   => __( 'Ajax-loading', 'userspace' ),
                        'cache'  => __( 'Caching', 'userspace' ),
                        'dialog' => __( 'Dialog box', 'userspace' )
                    )
                ),
                array(
                    'type'    => 'editor',
                    'tinymce' => true,
                    'slug'    => 'content',
                    'title'   => __( 'Content tab', 'userspace' ),
                    'notice'  => __( 'Supported shortcodes and HTML-code', 'userspace' )
                )
            )
        ) );

        $this->setup_tabs();

        add_filter( 'usp_field_options', array( $this, 'edit_tab_options' ), 10, 3 );
    }

    function form_navi() {

        $areas = array(
            'area-menu'     => __( '"Menu" area', 'userspace' ),
            'area-actions'  => __( '"Actions" area', 'userspace' ),
            'area-counters' => __( '"Counters" area', 'userspace' )
        );

        $content = '<div class="usp-custom-fields-navi">';

        $content .= '<ul class="usp-types-list">';

        foreach ( $areas as $type => $name ) {

            $class = ($this->manager_id == $type) ? 'current-item' : '';

            $content .= '<li class="usps__inline ' . $class . '">'
                . '<a class="usps__inline usps__ai-center" href="' . admin_url( 'admin.php?page=usp-tabs-manager&area-type=' . $type ) . '">' . $name . '</a>'
                . '</li>';
        }

        $content .= '</ul>';

        $content .= '</div>';

        return $content;
    }

    function is_default_tab( $slug ) {

        if ( ! $tab = USP()->tabs()->tab( $slug ) )
            return false;

        return $tab->custom_tab ? false : true;
    }

    function setup_tabs() {

        $defaultTabs = $this->get_default_tabs();

        if ( $this->fields ) {

            foreach ( $this->fields as $k => $tab ) {

                if ( $this->is_default_tab( $tab->id ) ) {
                    $tab->set_prop( 'must_delete', false );
                } else {
                    if ( isset( $tab->{'default-tab'} ) ) {
                        unset( $this->fields[$k] );
                    }
                }
            }

            if ( $defaultTabs ) {
                foreach ( $defaultTabs as $tab ) {
                    if ( $this->is_active_field( $tab['slug'] ) )
                        continue;
                    $this->add_field( $tab );
                }
            }
        } else if ( $defaultTabs ) {

            foreach ( $defaultTabs as $tab ) {
                $this->add_field( $tab );
            }
        }
    }

    function get_default_tabs() {

        if ( ! USP()->tabs()->get_tabs() )
            return false;

        $fields = array();

        foreach ( USP()->tabs()->get_tabs() as $tab_id => $tab ) {

            if ( $tab->custom_tab )
                continue;

            if ( 'area-' . $tab->output != $this->manager_id )
                continue;

            $fields[] = array(
                'type-edit'   => false,
                'slug'        => $tab_id,
                'delete'      => false,
                'default-tab' => true,
                'type'        => 'custom',
                'must_delete' => false,
                'title'       => $tab->name,
                'icon'        => $tab->icon
            );
        }

        return $fields;
    }

    function edit_tab_options( $options, $field, $type ) {

        if ( ! $field->slug )
            return $options;

        if ( $this->is_default_tab( $field->slug ) ) {

            unset( $options['public-tab'] );
            unset( $options['supports-tab'] );
            unset( $options['content'] );
            unset( $options['slug'] );

            $options['icon']['placeholder'] = USP()->tabs()->tab( $field->slug )->icon;

            $options['default-tab'] = array(
                'type'  => 'hidden',
                'slug'  => 'default-tab',
                'value' => 1
            );
        } else {
            $options['custom-tab'] = array(
                'type'  => 'hidden',
                'slug'  => 'custom-tab',
                'value' => 1
            );
        }

        return $options;
    }

}
