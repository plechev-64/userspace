<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Field;

use UserSpace\Common\Module\Form\Src\Domain\Field\AbstractField;
use UserSpace\Core\Sanitizer\SanitizerRule;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Класс для текстового поля (input type="password").
 */
class Password extends AbstractField
{
    /**
     * @inheritDoc
     */
    public function renderInput(): string
    {
        $attributes = $this->renderAttributes([
            'type' => 'password',
            'value' => $this->value,
        ]);

        return "<input {$attributes}>";
    }

    protected function _getRenderableValue(): string
    {
        return (int)$this->value;
    }

    public static function getSanitizationRule(): string
    {
        return SanitizerRule::NO_HTML;
    }
}