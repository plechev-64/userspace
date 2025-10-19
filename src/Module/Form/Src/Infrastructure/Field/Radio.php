<?php

namespace UserSpace\Module\Form\Src\Infrastructure\Field;

use UserSpace\Module\Form\Src\Domain\Field\AbstractField;
use UserSpace\Module\Form\Src\Infrastructure\Field\DTO\RadioFieldDto;

// Защита от прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Класс для поля с радио-кнопками (input type="radio").
 */
class Radio extends AbstractField {

	protected array $options;

	public function __construct( RadioFieldDto $dto ) {
		parent::__construct( $dto );
		$this->options = $dto->options;
	}

    public function render(): string
    {
        return $this->renderLabel() . $this->renderInput();
    }

    public function renderInput(): string {
		$options_html = '';

		foreach ( $this->options as $option_value => $option_label ) {
			$attributes = $this->renderAttributes( [
				'type'  => 'radio',
				'value' => $option_value,
			] );

			$checked = checked( $this->value, $option_value, false );

			$options_html .= sprintf(
				'<label><input %s %s> %s</label>',
				$attributes,
				$checked,
				esc_html( $option_label )
			);
		}

		return '<div class="usp-radio-group">' . $options_html . '</div>';
	}

	public function validate(): bool {
		parent::validate();

		if ( ! empty( $this->value ) && ! isset( $this->options[ $this->value ] ) ) {
			$this->addError( sprintf( 'Выбрано недопустимое значение для поля "%s".', $this->label ) );
		}

		return $this->isValid();
	}

    public static function getSettingsFormConfig(): array
    {
        {
            $config = parent::getSettingsFormConfig();
            $config['options'] = [
                'type' => 'key_value_editor',
                'label' => __('Options', 'usp'),
            ];
            return $config;
        }
    }
}