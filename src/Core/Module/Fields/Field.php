<?php

namespace USP\Core\Module\Fields;

class Field {
	public static function setup( array $args ): ?FieldAbstract {

		if ( is_admin() ) {
			usp_awesome_font_style();
		}

		if ( isset( USP()->get_fields()[ $args['type'] ] ) ) {

			$className = USP()->get_fields()[ $args['type'] ]['class'];

			return new $className( $args );
		}

		return null;
	}

}
