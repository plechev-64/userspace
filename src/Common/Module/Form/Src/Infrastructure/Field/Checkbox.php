<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Field;

// Защита от прямого доступа к файлу
use UserSpace\Common\Module\Form\Src\Domain\Field\AbstractField;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\CheckboxFieldDto;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Класс для группы полей-чекбоксов (множественный выбор).
 */
class Checkbox extends AbstractField {

    protected array $options;

	/**
	 * @param CheckboxFieldDto $dto
	 */
	public function __construct( CheckboxFieldDto $dto ) {
		parent::__construct( $dto );
        $this->options = $dto->options;
	}

	public function renderInput(): string {
		$options_html = '';
		$options      = $this->options ?? [];
		$currentValues = (array) $this->value; // Убедимся, что работаем с массивом

		foreach ($options as $value => $label) {
			$option_attributes = [
				'type'  => 'checkbox',
				'name'  => $this->name . '[]', // Отправляем как массив
				'value' => $value,
			];
			$checked = checked(true, in_array($value, $currentValues), false);

			$options_html .= sprintf('<label><input %s %s> %s</label>', $this->renderAttributes($option_attributes, false), $checked, esc_html($label));
		}

		return '<div class="usp-checkbox-group">' . $options_html . '</div>';
	}
}