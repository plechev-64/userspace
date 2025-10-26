<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Field;

use UserSpace\Common\Module\Form\Src\Domain\Field\AbstractField;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Класс для текстового поля (input type="text").
 */
class Text extends AbstractField
{
    /**
     * @inheritDoc
     */
    public function renderInput(): string
    {
        $attributes = $this->renderAttributes([
            'type' => 'text',
            'value' => $this->value,
        ]);

        return "<input {$attributes}>";
    }

    protected function _getRenderableValue(): string
    {
        if (is_array($this->value)) {
            return implode(', ', $this->value);
        }

        return (string)$this->value;
    }
}