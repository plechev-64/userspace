<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Field;

use UserSpace\Common\Module\Form\Src\Domain\Field\AbstractField;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\SelectFieldDto;

// Защита от прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Класс для выпадающего списка (select).
 */
class Select extends AbstractField {

	protected array $options;

	/**
	 * @param SelectFieldDto $dto Объект с данными для создания поля.
	 */
	public function __construct( SelectFieldDto $dto ) {
		parent::__construct( $dto );
		$this->options = $dto->options;
	}

	/**
	 * @inheritDoc
	 */
	public function renderInput(): string {
		$attributes = $this->renderAttributes();
		$options_html = '';

		foreach ( $this->options as $option_value => $option_label ) {
			$selected = selected( $this->value, $option_value, false );
			$options_html .= sprintf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $option_value ),
				$selected,
				esc_html( $option_label )
			);
		}

		return "<select {$attributes}>{$options_html}</select>";
	}

	/**
	 * @inheritDoc
	 */
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