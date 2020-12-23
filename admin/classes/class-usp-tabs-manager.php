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
                    'title'       => __( 'Icon class of usp-awesome', 'usp' )
                ),
                array(
                    'type'   => 'radio',
                    'slug'   => 'hidden',
                    'title'  => __( 'Hidden tab', 'usp' ),
                    'notice' => __( 'The tab will be available only by link', 'usp' ),
                    'values' => array(
                        __( 'No', 'usp' ),
                        __( 'Yes', 'usp' )
                    )
                ),
                array(
                    'type'        => 'text',
                    'slug'        => 'icon',
                    'class'       => 'usp-iconpicker',
                    'title'       => __( 'Icon class', 'usp' ),
                    'placeholder' => __( 'Example, fa-user', 'usp' )
                ),
                array(
                    'type'   => 'select',
                    'slug'   => 'public-tab',
                    'title'  => __( 'Tab privacy', 'usp' ),
                    'values' => array(
                        __( 'Private', 'usp' ),
                        __( 'Public', 'usp' )
                    )
                ),
                array(
                    'type'   => 'checkbox',
                    'slug'   => 'supports-tab',
                    'title'  => __( 'Support of the functions', 'usp' ),
                    'values' => array(
                        'ajax'   => __( 'ajax-loading', 'usp' ),
                        'cache'  => __( 'caching', 'usp' ),
                        'dialog' => __( 'dialog box', 'usp' )
                    )
                ),
                array(
                    'type'    => 'editor',
                    'tinymce' => true,
                    'slug'    => 'content',
                    'title'   => __( 'Content tab', 'usp' ),
                    'notice'  => __( 'supported shortcodes and HTML-code', 'usp' )
                )
            )
        ) );

        $this->setup_tabs();

        add_filter( 'usp_field_options', array( $this, 'edit_tab_options' ), 10, 3 );
    }

    function form_navi() {

        $areas = array(
            'area-menu'     => __( '"Menu" area', 'usp' ),
            'area-actions'  => __( '"Actions" area', 'usp' ),
            'area-counters' => __( '"Counters" area', 'usp' )
        );

        $content = '<div class="usp-custom-fields-navi">';

        $content .= '<ul class="usp-types-list">';

        foreach ( $areas as $type => $name ) {

            $class = ($this->manager_id == $type) ? 'class="current-item"' : '';

            $content .= '<li ' . $class . '><a href="' . admin_url( 'admin.php?page=usp-tabs-manager&area-type=' . $type ) . '">' . $name . '</a></li>';
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

        if ( ! USP()->tabs )
            return false;

        $fields = array();

        foreach ( USP()->tabs as $tab_id => $tab ) {

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
