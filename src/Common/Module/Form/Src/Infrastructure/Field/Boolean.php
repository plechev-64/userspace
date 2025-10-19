<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Field;

use UserSpace\Common\Module\Form\Src\Domain\Field\AbstractField;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\BooleanFieldDto;

/**
 * Класс для поля-переключателя (одиночный чекбокс, boolean).
 */
class Boolean extends AbstractField
{
    public function __construct(BooleanFieldDto $dto)
    {
        parent::__construct($dto);
    }

    public function render(): string
    {
        // Для одиночного чекбокса label оборачивает input
        $attributes = $this->renderAttributes(['type' => 'checkbox', 'value' => '1']);
        $checked = checked('1', $this->value, false);

        return sprintf('<label><input %s %s> %s</label>', $attributes, $checked, $this->str->escHtml($this->label));
    }

    public function renderInput(): string
    {
        $attributes = $this->renderAttributes(['type' => 'checkbox', 'value' => '1']);
        $checked = checked('1', $this->value, false);

        return sprintf('<input %s %s>', $attributes, $checked);
    }
}