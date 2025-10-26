<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Field;

use UserSpace\Common\Module\Form\Src\Domain\Field\AbstractField;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Класс для текстового поля (input type="number").
 */
class Number extends AbstractField
{
    /**
     * @inheritDoc
     */
    public function renderInput(): string
    {
        $attributes = $this->renderAttributes([
            'type' => 'number',
            'value' => $this->value,
        ]);

        return "<input {$attributes}>";
    }

    protected function _getRenderableValue(): string
    {
        return (int)$this->value;
    }
}