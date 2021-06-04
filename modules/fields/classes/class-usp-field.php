<?php

class USP_Field {
    static function setup( $args ) {

        if ( is_admin() ) {
            usp_awesome_font_style();
        }

        if ( isset( USP()->fields[$args['type']] ) ) {

            $className = USP()->fields[$args['type']]['class'];

            return new $className( $args );
        }
    }

}
