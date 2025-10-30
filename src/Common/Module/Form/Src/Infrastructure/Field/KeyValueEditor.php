<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Field;

use UserSpace\Common\Module\Form\Src\Domain\Field\AbstractField;
use UserSpace\Core\Sanitizer\SanitizerRule;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Класс для поля-редактора пар "ключ-значение".
 */
class KeyValueEditor extends AbstractField
{
    public function renderInput(): string
    {
        $pairsHtml = '';
        $values = is_array($this->value) ? $this->value : [];

        foreach ($values as $key => $val) {
            $pairsHtml .= $this->renderPair($key, $val);
        }

        $output = '<div class="usp-kv-editor" data-kv-editor-name="' . $this->str->escAttr($this->name) . '">';
        $output .= '<div class="usp-kv-pairs">' . $pairsHtml . '</div>';
        $output .= '<button type="button" class="button usp-kv-add">' . $this->str->translate('Add Option') . '</button>';
        $output .= '</div>';

        return $output;
    }

    private function renderPair(string $key = '', string $val = ''): string
    {
        $name = $this->str->escAttr($this->name);
        return '
            <div class="usp-kv-pair">
                <input type="text" name="' . $name . '[keys][]" class="usp-kv-key" placeholder="' . $this->str->escAttr($this->str->translate('Value')) . '" value="' . $this->str->escAttr($key) . '">
                <input type="text" name="' . $name . '[values][]" class="usp-kv-value" placeholder="' . $this->str->escAttr($this->str->translate('Label')) . '" value="' . $this->str->escAttr($val) . '">
                <button type="button" class="button button-link-delete usp-kv-remove">&times;</button>
            </div>';
    }

    protected function _getRenderableValue(): string
    {
        if (empty($this->value) || !is_array($this->value)) {
            return '';
        }

        $renderedPairs = [];
        foreach ($this->value as $key => $val) {
            // Формируем строку "Ключ: Значение"
            $renderedPairs[] = "{$key}: {$val}";
        }

        return implode(', ', $renderedPairs);
    }

    public static function getSanitizationRule(): string
    {
        return SanitizerRule::TEXT_FIELD;
    }
}