<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Field;

use UserSpace\Adapters\StringFilter;
use UserSpace\Common\Module\Form\Src\Domain\Field\AbstractField;
use UserSpace\Common\Module\Form\Src\Domain\Field\DTO\AbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Domain\Field\DTO\FieldDtoInterface;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\RadioFieldDto;
use UserSpace\Core\Sanitizer\SanitizerRule;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Класс для поля с радио-кнопками (input type="radio").
 */
class Radio extends AbstractField
{

    protected array $options;

    /**
     * @param RadioFieldDto $dto
     * @return void
     */
    public function init(FieldDtoInterface $dto): void
    {
        parent::init($dto);
        $this->options = $dto->options;
    }

    public function renderInput(): string
    {
        $options_html = '';

        foreach ($this->options as $option_value => $option_label) {
            $attributes = $this->renderAttributes([
                'type' => 'radio',
                'value' => $option_value,
            ]);

            $checked = checked($this->value, $option_value, false);

            $options_html .= sprintf(
                '<label><input %s %s> %s</label>',
                $attributes,
                $checked,
                $this->str->escHtml($option_label)
            );
        }

        return '<div class="usp-radio-group">' . $options_html . '</div>';
    }

    public function validate(): bool
    {
        parent::validate();

        if (!empty($this->value) && !isset($this->options[$this->value])) {
            $this->addError(sprintf($this->str->translate('Invalid value selected for field "%s".'), $this->label));
        }

        return $this->isValid();
    }

    public function getSettingsFormConfig(): array
    {
        $config = parent::getSettingsFormConfig();
        $config['options'] = [
            'type' => 'key_value_editor',
            'label' => $this->str->translate('Options'),
        ];
        return $config;
    }

    protected function _getRenderableValue(): string
    {
        if (empty($this->value)) {
            return '';
        }

        // Возвращаем метку опции, а не ее ключ.
        return $this->options[$this->value] ?? (string)$this->value;
    }

    public static function getSanitizationRule(): string
    {
        return SanitizerRule::KEY;
    }
}