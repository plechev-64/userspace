<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Field;

use UserSpace\Common\Module\Form\Src\Domain\Field\AbstractField;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\UrlAbstractFieldDto;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Класс для поля URL (input type="url").
 */
class Url extends AbstractField
{

    public function __construct(UrlAbstractFieldDto $dto)
    {
        parent::__construct($dto);
    }

    /**
     * @inheritDoc
     */
    public function renderInput(): string
    {
        $attributes = $this->renderAttributes([
            'type' => 'url',
            'value' => $this->value,
        ]);

        return "<input {$attributes}>";
    }
}