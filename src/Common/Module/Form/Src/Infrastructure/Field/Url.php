<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Field;

use UserSpace\Common\Module\Form\Src\Domain\Field\AbstractField;
use UserSpace\Core\Sanitizer\SanitizerRule;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Класс для поля URL (input type="url").
 */
class Url extends AbstractField
{
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

    protected function _getRenderableValue(): string
    {
        if (empty($this->value)) {
            return '';
        }

        $url = (string)$this->value;

        // Возвращаем кликабельную ссылку
        return sprintf(
            '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
            $this->str->escUrl($url),
            $this->str->escHtml($url)
        );
    }

    public static function getSanitizationRule(): string
    {
        return SanitizerRule::URL;
    }
}