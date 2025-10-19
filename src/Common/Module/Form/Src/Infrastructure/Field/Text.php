<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Field;

use UserSpace\Common\Module\Form\Src\Domain\Field\AbstractField;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\TextFieldDto;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Класс для текстового поля (input type="text").
 */
class Text extends AbstractField
{

    public function __construct(TextFieldDto $dto)
    {
        parent::__construct($dto);
    }

    /**
     * @inheritDoc
     */
    public function render(): string
    {
        $attributes = $this->renderAttributes([
            'type' => 'text',
            'value' => $this->value,
        ]);

        return $this->renderLabel() . "<input {$attributes}>";
    }
}