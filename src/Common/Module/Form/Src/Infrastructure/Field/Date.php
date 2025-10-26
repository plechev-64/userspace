<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Field;

use UserSpace\Common\Module\Form\Src\Domain\Field\AbstractField;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Класс для поля даты (input type="date").
 */
class Date extends AbstractField
{
    /**
     * @inheritDoc
     */
    public function renderInput(): string
    {
        $attributes = $this->renderAttributes([
            'type' => 'date',
            'value' => $this->value,
        ]);

        return "<input {$attributes}>";
    }

    protected function _getRenderableValue(): string
    {
        if (empty($this->value)) {
            return '';
        }

        try {
            $date = new \DateTime($this->value);
            // Форматируем дату в более привычный для пользователя вид.
            return $date->format('d.m.Y');
        } catch (\Exception $e) {
            // Если дата в некорректном формате, возвращаем исходное значение.
            return (string)$this->value;
        }
    }
}