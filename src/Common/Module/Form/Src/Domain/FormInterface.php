<?php

namespace UserSpace\Common\Module\Form\Src\Domain;

use UserSpace\Common\Module\Form\Src\Domain\Field\FieldInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Интерфейс для объекта формы.
 */
interface FormInterface {
	/**
	 * Генерирует HTML-код всей формы.
	 *
	 * @param bool $isAdminContext Указывает, происходит ли рендеринг в админ-контексте.
	 *
	 * @return string
	 */
	public function render( bool $isAdminContext = false ): string;

	/**
	 * Валидирует все поля формы.
	 *
	 * @return bool True, если все поля валидны, иначе false.
	 */
	public function validate(): bool;

	/**
	 * Возвращает массив всех ошибок валидации.
	 *
	 * @return string[]
	 */
	public function getErrors(): array;

	/**
	 * Возвращает плоский массив всех полей формы.
	 * @return FieldInterface[]
	 */
	public function getFields(): array;
}