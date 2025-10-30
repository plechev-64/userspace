<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Field;

use UserSpace\Common\Module\Form\Src\Domain\Field\AbstractField;
use UserSpace\Core\Sanitizer\SanitizerRule;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Класс для многострочного текстового поля (textarea).
 */
class Textarea extends AbstractField
{
    /**
     * @inheritDoc
     */
    public function renderInput(): string
    {
        $attributes = $this->renderAttributes();

        return "<textarea {$attributes}>" . $this->str->escTextarea($this->value) . '</textarea>';
    }

    protected function _getRenderableValue(): string
    {
        return (string)$this->value;
    }

    public static function getSanitizationRule(): string
    {
        return SanitizerRule::KSES_POST;
    }
}