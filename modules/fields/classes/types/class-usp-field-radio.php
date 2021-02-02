<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class-usp-custom-field-text
 *
 * @author Андрей
 */
class USP_Field_Radio extends USP_Field_Abstract {

    public $required;
    public $values;
    public $display = 'inline';
    public $empty_first;
    public $empty_value;
    public $childrens;
    public $value_in_key;

    function __construct( $args ) {

        if ( isset( $args['empty-first'] ) )
            $args['empty_first'] = $args['empty-first'];

        if ( isset( $args['empty-value'] ) )
            $args['empty_value'] = $args['empty-value'];

        parent::__construct( $args );
    }

    function get_options() {

        return array(
            array(
                'slug'    => 'empty_first',
                'default' => $this->empty_first,
                'type'    => 'text',
                'title'   => __( 'First value', 'userspace' ),
                'notice'  => __( 'Name of the first blank value, for example: "Not selected"', 'userspace' )
            ),
            array(
                'slug'    => 'values',
                'default' => $this->values,
                'type'    => 'dynamic',
                'title'   => __( 'Specify options', 'userspace' ),
                'notice'  => __( 'Specify each option in a separate field', 'userspace' )
            )
        );
    }

    function get_input() {

        if ( ! $this->values )
            return false;

        $content = '';

        if ( $this->empty_first ) {
            $content .= '<span class="usp-radio-box checkbox-display-' . $this->display . ' usps__inline usps__relative">';
            $content .= '<input type="radio" ' . $this->get_required() . ' ' . checked( $this->value, '', false ) . ' id="' . $this->input_id . '_' . $this->rand . '" data-slug="' . $this->slug . '" name="' . $this->input_name . '" value="' . $this->empty_value . '"> ';
            $content .= '<label class="usp-label usps usps__ai-center usps__no-select" for="' . $this->input_id . '_' . $this->rand . '">' . $this->empty_first . '</label>';
            $content .= '</span>';
        }

        $a = 0;

        if ( ! $this->empty_first && ! $this->value )
            $this->value = ($this->value_in_key) ? $this->values[0] : 0;

        foreach ( $this->values as $k => $value ) {

            if ( $this->value_in_key )
                $k = $value;

            $k = trim( $k );

            $content .= '<span class="usp-radio-box checkbox-display-' . $this->display . ' usps__inline usps__relative" data-value="' . $k . '">';
            $content .= '<input type="radio" ' . $this->get_required() . ' ' . checked( $this->value, $k, false ) . ' ' . $this->get_class() . ' id="' . $this->input_id . '_' . $k . $this->rand . '" data-slug="' . $this->slug . '" name="' . $this->input_name . '" value="' . $k . '"> ';
            $content .= '<label class="usp-label usps usps__ai-center usps__no-select" for="' . $this->input_id . '_' . $k . $this->rand . '">' . $value . '</label>';
            $content .= '</span>';

            $a ++;
        }

        return $content;
    }

    function get_filter_value() {
        return '<a href="' . $this->get_filter_url() . '" target="_blank">' . $this->value . '</a>';
    }

}
