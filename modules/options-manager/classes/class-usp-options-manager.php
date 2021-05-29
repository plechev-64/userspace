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
                'class'   => [ 'usp-options-bttn' ],
                'status'  => $box->active ? 'active' : ''
                ) );
        }

        $content = '<div class="usp-options-tabs usp-wrap__wiget">';

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

        $content = '<form method="post" id="usp-options" class="usp-options' . ($this->extend_options ? '' : ' usp-hide-more') . '" action="' . $this->action . '">';

        $content .= '<input type="hidden" name="page_options" value="' . $this->page_options . '">';

        $content .= wp_nonce_field( $this->nonce, '_wpnonce', true, false );

        $content .= '<div class="usp-option-menu usp-wrap__wiget">';

        if ( $this->extends ) {
            $content .= usp_get_button( array(
                'label'   => __( 'Advanced settings', 'userspace' ),
                'onclick' => 'return usp_enable_extend_options(this);',
                'icon'    => 'fa-square',
                'type'    => 'simple',
                'class'   => [ 'usp-toggle-extend', $this->extend_options ? 'usp-toggle-extend-show' : '' ],
                )
            );
        }

        foreach ( $this->boxes as $box ) {

            if ( ! $box->active )
                continue;

            $content .= usp_get_button( array(
                'label'      => $box->title,
                'onclick'    => 'usp_show_options_menu(this);return false;',
                'icon'       => 'fa-angle-down',
                'icon_align' => 'right',
                'type'       => 'clear',
                'style'      => 'text-align: center;',
                'fullwidth'  => true,
                'class'      => [ 'active-menu-item button button-primary button-large' ]
                )
            );
        }

        $content .= $this->get_menu();

        $content .= usp_get_button( array(
            'label'     => __( 'Save settings', 'userspace' ),
            'onclick'   => $this->onclick ? $this->onclick : false,
            'submit'    => $this->onclick ? false : true,
            'icon'      => 'fa-save',
            'type'      => 'clear',
            'size'      => 'medium',
            'fullwidth' => true,
            'class'     => [ 'usp-submit-options button button-primary' ]
            ) );

        $content .= '</div>';

        $content .= '<div class="usp-option-fields">';

        foreach ( $this->boxes as $box ) {

            $content .= $box->get_content();
        }

        $content .= '</div>';

        $content .= '</form>';

        return $content;
    }

}
