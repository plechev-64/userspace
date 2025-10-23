<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Field;

use UserSpace\Common\Module\Form\Src\Domain\Field\AbstractField;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\TextareaAbstractFieldDto;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Класс для многострочного текстового поля (textarea).
 */
class Textarea extends AbstractField
{

    public function __construct(TextareaAbstractFieldDto $dto)
    {
        parent::__construct($dto);
    }

    /**
     * @inheritDoc
     */
    public function renderInput(): string
    {
        $attributes = $this->renderAttributes();

        return "<textarea {$attributes}>" . $this->str->escTextarea($this->value) . '</textarea>';
    }
}