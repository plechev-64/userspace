<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Form;

use UserSpace\Adapters\StringFilter;
use UserSpace\Common\Module\Form\Src\Domain\Field\FieldInterface;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Представляет собой вертикальный блок внутри секции формы.
 */
class Block
{
    private StringFilter $str;

    /**
     * @param string $title Заголовок блока.
     * @param FieldInterface[] $fields Массив полей в блоке.
     */
    public function __construct(
        private readonly string $title,
        private readonly array  $fields
    )
    {
        $this->str = new StringFilter();
    }

    /**
     * @return FieldInterface[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function render(bool $isAdminContext = false): string
    {
        if ($isAdminContext) {
            $output = '';
            if (!empty($this->title)) {
                $output .= '<h3>' . $this->str->escHtml($this->title) . '</h3>';
            }
            $output .= '<table class="form-table" role="presentation">';
            foreach ($this->fields as $field) {
                $output .= '<tr class="usp-form-field-wrapper"><th>' . $field->renderLabel() . '</th><td>' . $field->renderInput() . '</td></tr>';
            }
            $output .= '</table>';
            return $output;
        }

        // Стандартный рендеринг для фронтенда
        $output = '<div class="usp-form-block">';
        if (!empty($this->title)) {
            $output .= '<h4 class="usp-form-block-title">' . $this->str->escHtml($this->title) . '</h4>';
        }
        foreach ($this->fields as $field) {
            $output .= '<div class="usp-form-field-wrapper">' . $field->render() . '</div>';
        }
        $output .= '</div>';

        return $output;
    }
}