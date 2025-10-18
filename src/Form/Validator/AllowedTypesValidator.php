<?php

namespace UserSpace\Form\Validator;

use UserSpace\Core\Form\FileValidatorInterface;

// Защита от прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AllowedTypesValidator implements FileValidatorInterface {

	private array $allowedTypes;

	/**
	 * @param string|array $allowedTypes Кома-сепарированная строка или массив MIME-типов.
	 */
	public function __construct( $allowedTypes ) {
		if ( is_string( $allowedTypes ) ) {
			$this->allowedTypes = array_map( 'trim', explode( ',', $allowedTypes ) );
		} else {
			$this->allowedTypes = (array) $allowedTypes;
		}
	}

	/**
	 * Валидирует файл из массива $_FILES.
	 *
	 * @param array $file
	 * @return string|null
	 */
	public function validate( array $file ): ?string {
		if ( ! in_array( $file['type'], $this->allowedTypes, true ) ) {
			return sprintf( __( 'Invalid file type. Allowed types: %s.', 'usp' ), implode( ', ', $this->allowedTypes ) );
		}
		return null;
	}

	public function getAllowedTypes(): array
	{
		return $this->allowedTypes;
	}
}