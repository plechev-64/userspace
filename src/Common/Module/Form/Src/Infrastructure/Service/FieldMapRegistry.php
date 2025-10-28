<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Service;

use InvalidArgumentException;
use UserSpace\Common\Module\Form\Src\Domain\Field\DTO\AbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Domain\Field\FieldInterface;
use UserSpace\Common\Module\Form\Src\Domain\Field\FieldType;
use UserSpace\Common\Module\Form\Src\Domain\Service\FieldMapRegistryInterface;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\Boolean;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\Checkbox;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\Date;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\BooleanFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\CheckboxFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\DateFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\EmailFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\KeyValueEditorFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\NumberFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\PasswordFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\RadioFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\SelectFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\TextFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\TextareaFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\UploaderFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\UrlFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\Email;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\KeyValueEditor;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\Number;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\Password;
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
class FieldMapRegistry implements FieldMapRegistryInterface
{
    /**
     * @var array<string, FieldMap>
     */
    private array $map;

    public function __construct()
    {
        // Регистрация всех стандартных типов полей
        $this->map = [
            FieldType::BOOLEAN->value => new FieldMap(Boolean::class, BooleanFieldDto::class),
            FieldType::TEXT->value => new FieldMap(Text::class, TextFieldDto::class),
            FieldType::EMAIL->value => new FieldMap(Email::class, EmailFieldDto::class),
            FieldType::CHECKBOX->value => new FieldMap(Checkbox::class, CheckboxFieldDto::class),
            FieldType::DATE->value => new FieldMap(Date::class, DateFieldDto::class),
            FieldType::RADIO->value => new FieldMap(Radio::class, RadioFieldDto::class),
            FieldType::SELECT->value => new FieldMap(Select::class, SelectFieldDto::class),
            FieldType::TEXTAREA->value => new FieldMap(Textarea::class, TextareaFieldDto::class),
            FieldType::URL->value => new FieldMap(Url::class, UrlFieldDto::class),
            FieldType::UPLOADER->value => new FieldMap(Uploader::class, UploaderFieldDto::class),
            FieldType::KEY_VALUE_EDITOR->value => new FieldMap(KeyValueEditor::class, KeyValueEditorFieldDto::class),
            FieldType::NUMBER->value => new FieldMap(Number::class, NumberFieldDto::class),
            FieldType::PASSWORD->value => new FieldMap(Password::class, PasswordFieldDto::class),
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

        return $this->map[$type]->getFieldClass();
    }

    /**
     * Возвращает имя DTO-класса для указанного типа поля.
     *
     * @param string $type
     *
     * @return class-string<AbstractFieldDto>
     * @throws InvalidArgumentException
     */
    public function getDtoClass(string $type): string
    {
        if (!$this->has($type)) {
            throw new InvalidArgumentException("Тип поля '{$type}' не поддерживается.");
        }

        return $this->map[$type]->getDtoClass();
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
     * @return array<string, FieldMap>
     */
    public function getMap(): array
    {
        return $this->map;
    }

    /**
     * Возвращает всю карту полей в виде массива.
     * @return array<string, array{class: class-string<FieldInterface>, dto: class-string<AbstractFieldDto>}>
     */
    public function toArray(): array
    {
        return array_map(function ($fieldMap) {
            return $fieldMap->toArray();
        }, $this->map);
    }

    public function register(string $type, FieldMap $fieldMap): void
    {
        $this->map[$type] = $fieldMap;
    }
}