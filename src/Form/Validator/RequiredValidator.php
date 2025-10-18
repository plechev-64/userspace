<?php

namespace UserSpace\Form\Validator;

use UserSpace\Core\Form\Field\FieldInterface;
use UserSpace\Core\Form\ValidatorInterface;

// Защита от прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RequiredValidator implements ValidatorInterface {

	public function validate( FieldInterface $field ): ?string {
		if ( empty( $field->getValue() ) ) {
			return sprintf( __( 'Field "%s" is required.', 'usp' ), $field->getLabel() );
		}

		return null;
	}
}