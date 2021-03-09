<?php

class USP_Field_Hidden extends USP_Field_Abstract {

    public $required;
    public $values = array();
    public $placeholder;
    public $maxlength;
    public $pattern;
    public $class;

    function __construct( $args ) {
        parent::__construct( $args );
    }

    function get_options() {

        return array(
            array(
                'slug'    => 'placeholder',
                'default' => $this->placeholder,
                'type'    => 'text',
                'title'   => __( 'Placeholder', 'userspace' )
            ),
            array(
                'slug'    => 'maxlength',
                'default' => $this->maxlength,
                'type'    => 'number',
                'title'   => __( 'Maxlength', 'userspace' ),
                'notice'  => __( 'Maximum number of symbols per field', 'userspace' )
            ),
            array(
                'slug'    => 'pattern',
                'default' => $this->pattern,
                'type'    => 'text',
                'title'   => __( 'Pattern', 'userspace' )
            )
        );
    }

    function get_field_input() {
        return $this->get_input();
    }

    function get_field_html( $args = false ) {
        return $this->get_field_input();
    }

    function get_input() {

        if ( $this->values && is_array( $this->values ) ) {

            $content = '';
            foreach ( $this->values as $value ) {
                $content .= '<input type="' . $this->type . '" ' . $this->get_class() . ' name="' . $this->input_name . '[]" value=\'' . $value . '\'/>';
            }

            return $content;
        }

        return '<input type="' . $this->type . '" ' . $this->get_pattern() . ' ' . $this->get_maxlength() . ' ' . $this->get_required() . ' ' . $this->get_placeholder() . ' ' . $this->get_class() . ' name="' . $this->input_name . '" id="' . $this->input_id . '" value=\'' . $this->value . '\'/>';
    }

}
