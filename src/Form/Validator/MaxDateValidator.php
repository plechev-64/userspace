<?php

namespace UserSpace\Form\Validator;

use DateTime;
use Exception;
use UserSpace\Core\Form\Field\FieldInterface;
use UserSpace\Core\Form\ValidatorInterface;

// Защита от прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MaxDateValidator implements ValidatorInterface {

	private string $maxDate;

	public function __construct( string $maxDate ) {
		$this->maxDate = $maxDate;
	}

	public function validate( FieldInterface $field ): ?string {
		if ( empty( $field->getValue() ) ) {
			return null;
		}

		try {
			$valueDate = new DateTime( (string) $field->getValue() );
			$maxDate   = new DateTime( $this->maxDate );

			return $valueDate > $maxDate ? sprintf( __( 'Date in "%s" field cannot be later than %s.', 'usp' ), $field->getLabel(), $maxDate->format( 'Y-m-d' ) ) : null;
		} catch ( Exception $e ) {
			return sprintf( __( 'Invalid date format in "%s" field.', 'usp' ), $field->getLabel() );
		}
	}
}