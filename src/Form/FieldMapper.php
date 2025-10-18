<?php

namespace UserSpace\Form;

use InvalidArgumentException;
use UserSpace\Core\Form\Field\FieldInterface;
use UserSpace\Form\Field\Boolean;
use UserSpace\Form\Field\Checkbox;
use UserSpace\Form\Field\Date;
use UserSpace\Form\Field\DTO\BooleanFieldDto;
use UserSpace\Form\Field\DTO\CheckboxFieldDto;
use UserSpace\Form\Field\DTO\DateFieldDto;
use UserSpace\Form\Field\DTO\FieldDto;
use UserSpace\Form\Field\DTO\KeyValueEditorFieldDto;
use UserSpace\Form\Field\DTO\RadioFieldDto;
use UserSpace\Form\Field\DTO\SelectFieldDto;
use UserSpace\Form\Field\DTO\TextareaFieldDto;
use UserSpace\Form\Field\DTO\TextFieldDto;
use UserSpace\Form\Field\DTO\UploaderFieldDto;
use UserSpace\Form\Field\DTO\UrlFieldDto;
use UserSpace\Form\Field\KeyValueEditor;
use UserSpace\Form\Field\Radio;
use UserSpace\Form\Field\Select;
use UserSpace\Form\Field\Text;
use UserSpace\Form\Field\Textarea;
use UserSpace\Form\Field\Uploader;
use UserSpace\Form\Field\Url;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Карта соответствия типов полей их классам-реализациям.
 */
class FieldMapper
{

    /**
     * @var array<string, array{class: class-string<FieldInterface>, dto: class-string<FieldDto>}>
     */
    private array $map;

    public function __construct()
    {
        $this->map = [
            'boolean' => ['class' => Boolean::class, 'dto' => BooleanFieldDto::class],
            'text' => ['class' => Text::class, 'dto' => TextFieldDto::class],
            'checkbox' => ['class' => Checkbox::class, 'dto' => CheckboxFieldDto::class],
            'date' => ['class' => Date::class, 'dto' => DateFieldDto::class],
            'radio' => ['class' => Radio::class, 'dto' => RadioFieldDto::class],
            'select' => ['class' => Select::class, 'dto' => SelectFieldDto::class],
            'textarea' => ['class' => Textarea::class, 'dto' => TextareaFieldDto::class],
            'url' => ['class' => Url::class, 'dto' => UrlFieldDto::class],
            'uploader' => ['class' => Uploader::class, 'dto' => UploaderFieldDto::class],
            'key_value_editor' => ['class' => KeyValueEditor::class, 'dto' => KeyValueEditorFieldDto::class],
        ];
    }

    /**
     * Возвращает имя класса для указанного типа поля.
     *
     * @param string $type Тип поля.
     *
     * @return class-string<FieldInterface>
     * @throws InvalidArgumentException Если тип поля не найден.
     */
    public function getClass(string $type): string
    {
        if (!$this->has($type)) {
            throw new InvalidArgumentException("Тип поля '{$type}' не поддерживается.");
        }

        return $this->map[$type]['class'];
    }

    /**
     * Возвращает имя DTO-класса для указанного типа поля.
     *
     * @param string $type
     *
     * @return class-string<FieldDto>
     * @throws InvalidArgumentException
     */
    public function getDtoClass(string $type): string
    {
        if (!$this->has($type)) {
            throw new InvalidArgumentException("Тип поля '{$type}' не поддерживается.");
        }

        return $this->map[$type]['dto'];
    }

    /**
     * Проверяет, существует ли реализация для указанного типа поля.
     *
     * @param string $type Тип поля.
     *
     * @return bool
     */
    public function has(string $type): bool
    {
        return isset($this->map[$type]);
    }

    /**
     * Возвращает всю карту полей.
     * @return array<string, array{class: class-string<FieldInterface>, dto: class-string<FieldDto>}>
     */
    public function getMap(): array
    {
        return $this->map;
    }
}