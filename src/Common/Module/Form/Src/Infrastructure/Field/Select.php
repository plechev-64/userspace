<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Field;

use UserSpace\Adapters\StringFilter;
use UserSpace\Common\Module\Form\Src\Domain\Field\AbstractField;
use UserSpace\Common\Module\Form\Src\Domain\Field\DTO\AbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\SelectAbstractFieldDto;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Класс для выпадающего списка (select).
 */
class Select extends AbstractField
{

    protected array $options;

    /**
     * @param SelectAbstractFieldDto $dto Объект с данными для создания поля.
     */
    public function init(AbstractFieldDto $dto): void
    {
        parent::init($dto);
        $this->options = $dto->options;
    }

    /**
     * @inheritDoc
     */
    public function renderInput(): string
    {
        $attributes = $this->renderAttributes();
        $options_html = '';

        foreach ($this->options as $option_value => $option_label) {
            $selected = selected($this->value, $option_value, false);
            $options_html .= sprintf(
                '<option value="%s" %s>%s</option>',
                $this->str->escAttr($option_value),
                $selected,
                $this->str->escHtml($option_label)
            );
        }

        return "<select {$attributes}>{$options_html}</select>";
    }

    /**
     * @inheritDoc
     */
    public function validate(): bool
    {
        parent::validate();

        if (!empty($this->value) && !isset($this->options[$this->value])) {
            $this->addError(sprintf('Выбрано недопустимое значение для поля "%s".', $this->label));
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
}