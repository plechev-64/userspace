<?php

namespace UserSpace\Module\Form\Src\Infrastructure\Validator;

use UserSpace\Module\Form\Src\Domain\Field\FieldInterface;
use UserSpace\Module\Form\Src\Domain\ValidatorInterface;

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