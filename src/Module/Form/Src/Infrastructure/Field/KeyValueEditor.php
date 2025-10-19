<?php

namespace UserSpace\Module\Form\Src\Infrastructure\Field;

use UserSpace\Module\Form\Src\Domain\Field\AbstractField;
use UserSpace\Module\Form\Src\Infrastructure\Field\DTO\KeyValueEditorFieldDto;

// Защита от прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Класс для поля-редактора пар "ключ-значение".
 */
class KeyValueEditor extends AbstractField {

	public function __construct( KeyValueEditorFieldDto $dto ) {
		parent::__construct( $dto );
	}

    public function render(): string
    {
        return $this->renderLabel() . $this->renderInput();
    }

    public function renderInput(): string {
		$pairsHtml = '';
		$values = is_array($this->value) ? $this->value : [];

		foreach ($values as $key => $val) {
			$pairsHtml .= $this->renderPair($key, $val);
		}

		$output = '<div class="usp-kv-editor" data-kv-editor-name="' . esc_attr($this->name) . '">';
		$output .= '<div class="usp-kv-pairs">' . $pairsHtml . '</div>';
		$output .= '<button type="button" class="button usp-kv-add">' . __('Add Option', 'usp') . '</button>';
		$output .= '</div>';

		return $output;
	}

	private function renderPair(string $key = '', string $val = ''): string
	{
		$name = esc_attr($this->name);
		return '
            <div class="usp-kv-pair">
                <input type="text" name="' . $name . '[keys][]" class="usp-kv-key" placeholder="' . __('Value', 'usp') . '" value="' . esc_attr($key) . '">
                <input type="text" name="' . $name . '[values][]" class="usp-kv-value" placeholder="' . __('Label', 'usp') . '" value="' . esc_attr($val) . '">
                <button type="button" class="button button-link-delete usp-kv-remove">&times;</button>
            </div>';
	}
}