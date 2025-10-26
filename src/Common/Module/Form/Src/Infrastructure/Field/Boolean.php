<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Field;

use UserSpace\Common\Module\Form\Src\Domain\Field\AbstractField;

/**
 * Класс для поля-переключателя (одиночный чекбокс, boolean).
 */
class Boolean extends AbstractField
{
    public function renderInput(): string
    {
        $attributes = $this->renderAttributes(['type' => 'checkbox', 'value' => '1']);
        $checked = checked('1', $this->value, false);

        // Для Boolean поля label должен быть рядом с чекбоксом
        return sprintf(
            '<label><input %s %s> %s</label>',
            $attributes, $checked, $this->str->escHtml($this->label) // Используем $this->label для текста рядом с чекбоксом
        );
    }
}