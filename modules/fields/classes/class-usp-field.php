<?php

class USP_Field {
    static function setup( $args ) {

        if ( is_admin() ) {
            usp_awesome_font_style();
        }

        if ( isset( USP()->get_fields()[$args['type']] ) ) {

            $className = USP()->get_fields()[$args['type']]['class'];

            return new $className( $args );
        }
    }

}
