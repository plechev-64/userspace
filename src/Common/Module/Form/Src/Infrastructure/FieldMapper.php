<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure;

use InvalidArgumentException;
use UserSpace\Common\Module\Form\Src\Domain\Field\DTO\FieldDto;
use UserSpace\Common\Module\Form\Src\Domain\Field\FieldInterface;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\Boolean;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\Checkbox;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\Date;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\BooleanFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\CheckboxFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\DateFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\KeyValueEditorFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\RadioFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\SelectFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\TextareaFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\TextFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\UploaderFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\UrlFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\KeyValueEditor;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\Radio;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\Select;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\Text;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\Textarea;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\Uploader;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\Url;

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