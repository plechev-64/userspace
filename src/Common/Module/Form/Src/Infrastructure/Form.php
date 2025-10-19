<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure;

// Защита от прямого доступа к файлу
use UserSpace\Common\Module\Form\Src\Domain\Field\FieldInterface;
use UserSpace\Common\Module\Form\Src\Domain\FormInterface;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Класс для управления формой и ее полями.
 */
class Form implements FormInterface
{

    /**
     * @var Section[] Массив секций формы.
     */
    private array $sections;

    /**
     * @var array Массив ошибок валидации.
     */
    private array $errors = [];

    /**
     * @param Section[] $sections Массив объектов секций.
     */
    public function __construct(array $sections)
    {
        $this->sections = $sections;
    }

    /**
     * Генерирует HTML-код всей формы.
     *
     * @param bool $isAdminContext Указывает, происходит ли рендеринг в админ-контексте (например, на странице профиля).
     * @return string
     */
    public function render(bool $isAdminContext = false): string
    {
        $output = '';
        if (!$isAdminContext) {
            $output .= '<div class="usp-form-wrapper">';
        }

        foreach ($this->sections as $section) {
            $output .= $section->render($isAdminContext);
        }

        if (!$isAdminContext) {
            $output .= '</div>';
        }

        return $output;
    }

    /**
     * Валидирует все поля формы.
     *
     * @return bool True, если все поля валидны, иначе false.
     */
    public function validate(): bool
    {
        $this->errors = [];
        $is_valid = true;

        foreach ($this->sections as $section) {
            foreach ($section->getBlocks() as $block) {
                foreach ($block->getFields() as $field) {
                    if (!$field->validate()) {
                        $is_valid = false;
                        $this->errors = array_merge($this->errors, $field->getErrors());
                    }
                }
            }
        }

        return $is_valid;
    }

    /**
     * Возвращает массив всех ошибок валидации.
     *
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Возвращает плоский массив всех полей формы.
     * @return FieldInterface[]
     */
    public function getFields(): array
    {
        $allFields = [];
        foreach ($this->sections as $section) {
            foreach ($section->getBlocks() as $block) {
                foreach ($block->getFields() as $field) {
                    $allFields[] = $field;
                }
            }
        }
        return $allFields;
    }
}