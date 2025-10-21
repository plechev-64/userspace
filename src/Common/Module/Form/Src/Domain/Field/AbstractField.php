<?php

namespace UserSpace\Common\Module\Form\Src\Domain\Field;

use UserSpace\Adapters\StringFilter;
use InvalidArgumentException;
use UserSpace\Common\Module\Form\Src\Domain\Field\DTO\FieldDto;
use UserSpace\Common\Module\Form\Src\Domain\ValidatorInterface;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Абстрактный базовый класс для всех полей формы.
 */
abstract class AbstractField implements FieldInterface
{

    protected string $name;
    protected string $label;
    protected mixed $value;
    /**
     * @var ValidatorInterface[]
     */
    protected array $attributes;
    protected array $rules;
    protected array $errors = [];
    protected StringFilter $str;

    /**
     * Конструктор поля.
     *
     * @param FieldDto $dto Объект с данными для создания поля.
     *
     * @throws InvalidArgumentException Если имя поля пустое.
     */
    public function __construct(FieldDto $dto)
    {
        if (empty(trim($dto->name))) {
            throw new InvalidArgumentException('Field name cannot be empty.');
        }
        $this->name = $dto->name;
        $this->label = $dto->label;
        $this->value = $dto->value;
        $this->attributes = $dto->attributes;
        $this->rules = $dto->rules;

        // Временное решение, пока нет DI в полях
        $this->str = new StringFilter();
    }

    /**
     * @inheritDoc
     */
    public function render(): string
    {
        // По умолчанию, полное отображение поля - это его метка и поле ввода.
        return $this->renderLabel() . $this->renderInput();
    }

    /**
     * Генерирует HTML для метки (label).
     *
     * @return string
     */
    public function renderLabel(): string
    {
        if (empty($this->label)) {
            return '';
        }

        $id = $this->attributes['id'] ?? 'field-' . $this->name;
        $required_indicator = !empty($this->rules['required']) ? ' <span class="usp-required">*</span>' : '';

        return sprintf('<label for="%s">%s%s</label>', $this->str->escAttr($id), $this->str->escHtml($this->label), $required_indicator);
    }

    /**
     * @inheritDoc
     */
    public function renderInput(): string
    {
        // Базовая реализация для простого текстового поля.
        // Дочерние классы будут переопределять этот метод.
        $attributes = $this->renderAttributes(['type' => 'text']);
        return sprintf('<input %s>', $attributes);
    }

    /**
     * Генерирует строку с HTML-атрибутами.
     *
     * @param array $additional_attributes Дополнительные атрибуты для слияния.
     *
     * @return string
     */
    protected function renderAttributes(array $additional_attributes = []): string
    {
        $default_attributes = [
            'id' => 'field-' . $this->name,
            'name' => $this->name,
        ];

        if (!empty($this->rules['required'])) {
            $default_attributes['required'] = true;
        }

        $attributes = array_merge($default_attributes, $this->attributes, $additional_attributes);
        $attribute_strings = [];

        foreach ($attributes as $key => $val) {
            if (is_bool($val)) {
                if ($val) {
                    $attribute_strings[] = $this->str->escAttr($key);
                }
            } else {
                $attribute_strings[] = sprintf('%s="%s"', $this->str->escAttr($key), $this->str->escAttr($val));
            }
        }

        return implode(' ', $attribute_strings);
    }

    /**
     * @inheritDoc
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function validate(): bool
    {
        $this->errors = [];

        foreach ($this->rules as $validator) {
            if (!$validator instanceof ValidatorInterface) {
                // Можно бросить исключение или просто проигнорировать
                continue;
            }
            $error = $validator->validate($this);
            if ($error !== null) {
                $this->addError($error);
            }
        }

        return $this->isValid();
    }

    /**
     * @inheritDoc
     */
    public function isValid(): bool
    {
        return empty($this->errors);
    }

    /**
     * @inheritDoc
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Добавляет сообщение об ошибке.
     *
     * @param string $message Сообщение об ошибке.
     */
    protected function addError(string $message): void
    {
        $this->errors[] = $message;
    }

    /**
     * Возвращает базовую конфигурацию формы для настроек.
     * @return array
     */
    public static function getSettingsFormConfig(): array
    {
        $str = new StringFilter();
        return [
            'label' => [
                'type' => 'text',
                'label' => $str->translate('Label'),
            ],
            'required' => [
                'type' => 'boolean',
                'label' => $str->translate('Required'),
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return $this->label;
    }
}