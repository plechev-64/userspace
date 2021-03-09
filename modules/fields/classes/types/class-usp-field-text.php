<?php

class USP_Field_Text extends USP_Field_Abstract {

    public $required;
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

    function get_input() {
        return '<input type="' . $this->type . '" ' . $this->get_pattern() . ' ' . $this->get_maxlength() . ' ' . $this->get_required() . ' ' . $this->get_placeholder() . ' ' . $this->get_class() . ' name="' . $this->input_name . '" id="' . $this->input_id . '" value=\'' . $this->value . '\'/>';
    }

    function get_value() {

        if ( ! $this->value )
            return false;

        if ( $this->type == 'email' )
            return '<a rel="nofollow" target="_blank" href="mailto:' . $this->value . '">' . $this->value . '</a>';
        if ( $this->type == 'url' )
            return '<a rel="nofollow" target="_blank" href="' . $this->value . '">' . $this->value . '</a>';

        return $this->value;
    }

    function get_filter_value() {
        return '<a href="' . $this->get_filter_url() . '" target="_blank">' . $this->value . '</a>';
    }

}
