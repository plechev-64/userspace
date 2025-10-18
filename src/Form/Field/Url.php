<?php

namespace UserSpace\Form\Field;

use UserSpace\Core\Form\Field\AbstractField;
use UserSpace\Form\Field\DTO\UrlFieldDto;

// Защита от прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Класс для поля URL (input type="url").
 */
class Url extends AbstractField {

	public function __construct( UrlFieldDto $dto ) {
		parent::__construct( $dto );
	}

	/**
	 * @inheritDoc
	 */
	public function renderInput(): string {
		$attributes = $this->renderAttributes( [
			'type'  => 'url',
			'value' => $this->value,
		] );

		return "<input {$attributes}>";
	}
}