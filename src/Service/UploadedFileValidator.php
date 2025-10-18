<?php

namespace UserSpace\Service;

use UserSpace\Core\Form\FileValidatorInterface;

// Защита от прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Сервис для валидации загружаемого файла на основе набора правил.
 */
class UploadedFileValidator {

	private array $errors = [];

	/**
	 * Валидирует файл из массива $_FILES.
	 *
	 * @param array                  $file  Массив файла из $_FILES.
	 * @param FileValidatorInterface[] $rules Массив объектов-валидаторов.
	 *
	 * @return bool
	 */
	public function validate( array $file, array $rules ): bool {
		$this->errors = [];

		if ( empty( $file ) || UPLOAD_ERR_NO_FILE === $file['error'] ) {
			return true; // Нет файла для валидации, считаем успешным.
		}

		foreach ( $rules as $rule ) {
			if ( $rule instanceof FileValidatorInterface && ( $error = $rule->validate( $file ) ) ) {
				$this->errors[] = $error;
			}
		}

		return empty( $this->errors );
	}

	/**
	 * Возвращает массив ошибок валидации.
	 *
	 * @return array
	 */
	public function getErrors(): array {
		return $this->errors;
	}
}