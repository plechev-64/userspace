<?php

class USP_Options_Manager {

    public $boxes          = array();
    public $extends        = false;
    public $extend_options = false;
    public $nonce          = 'update-options';
    public $page_options   = '';
    public $onclick        = 'usp_update_options();return false;';
    public $action         = 'options.php';
    public $method         = 'post';
    public $option_name;

    function __construct( $args = false ) {

        if ( $args ) {
            $this->init_properties( $args );
        }

        if ( $this->extends )
            $this->extend_options = isset( $_COOKIE['usp_extends'] ) ? $_COOKIE['usp_extends'] : 0;
    }

    function init_properties( $args ) {

        $properties = get_class_vars( get_class( $this ) );

        foreach ( $properties as $name => $val ) {
            if ( isset( $args[$name] ) & ! empty( $args[$name] ) )
                $this->$name = $args[$name];
        }
    }

    function isset_box( $box_id ) {
        return isset( $this->boxes[$box_id] );
    }

    function add_box( $box_id, $args ) {
        $this->boxes[$box_id] = new USP_Options_Box( $box_id, $args, $this->option_name );
        return $this->box( $box_id );
    }

    function box( $box_id ) {
        return $this->boxes[$box_id];
    }

    function get_menu() {

        if ( ! $this->boxes )
            return false;

        $items = [];

        foreach ( $this->boxes as $box ) {

            $items[] = usp_get_button( array(
                'data'    => array(
                    'options' => $box->box_id
                ),
                'label'   => $box->title,
                'href'    => admin_url( 'admin.php?page=' . $this->page_options . '&usp-options-box=' . $box->box_id ),
                'onclick' => 'usp_onclick_options_label(this);return false;',
                'icon'    => $box->icon,
                'type'    => 'simple',
                'status'  => $box->active ? 'active' : ''
                ) );
        }

        $content = '<div class="usp-menu menu-items">';

        foreach ( $items as $item ) {
            $content .= $item;
        }

        $content .= '</div>';

        return $content;
    }

    function get_content() {

        if ( ! isset( $_GET['usp-options-box'] ) ) {
            foreach ( $this->boxes as $id => $box ) {
                $this->boxes[$id]->active = true;
                break;
            }
        }

        $content = '<div class="usp-options-manager usp-options ' . ($this->extend_options ? 'show-extends-options' : 'hide-extends-options') . '">';

        $content .= '<form method="post" class="usp-options-form" action="' . $this->action . '">';

        $content .= '<input type="hidden" name="page_options" value="' . $this->page_options . '">';

        $content .= wp_nonce_field( $this->nonce, '_wpnonce', true, false );

        $content .= '<div class="options-menu-boxes">';

        if ( $this->extends ) {
            $content .= '<label class="usp-option-extend-switch">'
                . '<input type="checkbox" name="extend_options" ' . checked( $this->extend_options, 1, false ) . ' onclick="return usp_enable_extend_options(this);" value="1"> '
                . __( 'Advanced settings', 'usp' )
                . '</label>';
        }

        foreach ( $this->boxes as $box ) {

            if ( ! $box->active )
                continue;

            $content .= usp_get_button( array(
                'label'      => $box->title,
                'onclick'    => 'usp_show_options_menu(this);return false;',
                'icon'       => 'fa-chevron-down', //$box->icon,
                'icon_align' => 'right',
                'type'       => 'clear',
                'style'      => 'text-align: center;',
                'fullwidth'  => true,
                'class'      => array( 'button button-primary button-large active-menu-item' )
                //'status'	 => $box->active ? 'active' : ''
                )
            );
        }

        $content .= $this->get_menu();

        $content .= usp_get_button( array(
            'label'   => __( 'Save settings', 'usp' ),
            'onclick' => $this->onclick ? $this->onclick : false,
            'submit'  => $this->onclick ? false : true,
            'icon'    => 'fa-save',
            'type'    => 'clear',
            'style'   => 'text-align: center;',
            'class'   => array( 'button button-primary button-large usp-submit-options' )
            ) );

        $content .= '</div>';

        $content .= '<div class="options-form-boxes">';

        foreach ( $this->boxes as $box ) {

            $content .= $box->get_content();
        }

        $content .= '</div>';

        $content .= '</form>';

        $content .= '</div>';

        return $content;
    }

}
