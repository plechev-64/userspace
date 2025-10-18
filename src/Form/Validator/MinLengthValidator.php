<?php

namespace UserSpace\Form\Validator;

use UserSpace\Core\Form\Field\FieldInterface;
use UserSpace\Core\Form\ValidatorInterface;

// Защита от прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MinLengthValidator implements ValidatorInterface {

	private int $minLength;

	public function __construct( int $minLength ) {
		$this->minLength = $minLength;
	}

	public function validate( FieldInterface $field ): ?string {
		$value = (string) $field->getValue();
		if ( ! empty( $value ) && mb_strlen( $value ) < $this->minLength ) {
			return sprintf( __( 'Field "%s" must be at least %d characters long.', 'usp' ), $field->getLabel(), $this->minLength );
		}

		return null;
	}
}