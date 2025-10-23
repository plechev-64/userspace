<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Field;

use UserSpace\Common\Module\Form\Src\Domain\Field\AbstractField;
use UserSpace\Common\Module\Form\Src\Domain\Field\DTO\AbstractFieldDto;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Класс для скрытого поля (input type="hidden").
 */
class Hidden extends AbstractField
{

    public function __construct(AbstractFieldDto $dto)
    {
        parent::__construct($dto);
    }

    /**
     * @inheritDoc
     */
    public function renderInput(): string
    {
        $attributes = $this->renderAttributes([
            'type' => 'hidden',
            'value' => $this->value,
        ]);

        return "<input {$attributes}>";
    }
}