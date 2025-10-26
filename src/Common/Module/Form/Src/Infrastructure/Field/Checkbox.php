<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Field;

use UserSpace\Common\Module\Form\Src\Domain\Field\AbstractField;
use UserSpace\Common\Module\Form\Src\Domain\Field\DTO\AbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\CheckboxFieldDto;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Класс для группы полей-чекбоксов (множественный выбор).
 */
class Checkbox extends AbstractField
{

    protected array $options;

    /**
     * @param CheckboxFieldDto $dto
     */
    public function init(AbstractFieldDto $dto): void
    {
        parent::init($dto);
        $this->options = $dto->options;
    }

    public function renderInput(): string
    {
        $options_html = '';
        $options = $this->options ?? [];
        $currentValues = (array)$this->value; // Убедимся, что работаем с массивом

        foreach ($options as $value => $label) {
            $option_attributes = [
                'type' => 'checkbox',
                'name' => $this->name . '[]', // Отправляем как массив
                'value' => $value,
            ];
            $checked = checked(true, in_array($value, $currentValues), false);

            $options_html .= sprintf('<label><input %s %s> %s</label>', $this->renderAttributes($option_attributes, false), $checked, $this->str->escHtml($label));
        }

        return '<div class="usp-checkbox-group">' . $options_html . '</div>';
    }

    protected function _getRenderableValue(): string
    {
        $selectedValues = (array)$this->value;
        if (empty($selectedValues)) {
            return '';
        }

        $options = $this->options ?? [];
        $selectedLabels = [];

        foreach ($selectedValues as $value) {
            // Добавляем метку, только если она существует для данного значения
            if (isset($options[$value])) {
                $selectedLabels[] = $this->str->escHtml($options[$value]);
            }
        }

        return implode(', ', $selectedLabels);
    }
}