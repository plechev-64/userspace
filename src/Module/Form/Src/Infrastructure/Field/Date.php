<?php

namespace UserSpace\Module\Form\Src\Infrastructure\Field;

use UserSpace\Module\Form\Src\Domain\Field\AbstractField;
use UserSpace\Module\Form\Src\Infrastructure\Field\DTO\DateFieldDto;

// Защита от прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Класс для поля даты (input type="date").
 */
class Date extends AbstractField {

	public function __construct( DateFieldDto $dto ) {
		parent::__construct( $dto );
	}

	/**
	 * @inheritDoc
	 */
	public function renderInput(): string {
		$attributes = $this->renderAttributes( [
			'type'  => 'date',
			'value' => $this->value,
		] );

		return "<input {$attributes}>";
	}
}