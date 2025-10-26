<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Service;

use InvalidArgumentException;
use UserSpace\Common\Module\Form\Src\Domain\Field\DTO\AbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Domain\Field\FieldInterface;
use UserSpace\Common\Module\Form\Src\Domain\Service\FieldMapRegistryInterface;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\Boolean;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\Checkbox;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\Date;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\BooleanAbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\CheckboxAbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\DateAbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\KeyValueEditorAbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\RadioAbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\SelectAbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\TextAbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\TextareaAbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\UploaderAbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\UrlAbstractFieldDto;
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
            'boolean' => new FieldMap(Boolean::class, BooleanAbstractFieldDto::class),
            'text' => new FieldMap(Text::class, TextAbstractFieldDto::class),
            'checkbox' => new FieldMap(Checkbox::class, CheckboxAbstractFieldDto::class),
            'date' => new FieldMap(Date::class, DateAbstractFieldDto::class),
            'radio' => new FieldMap(Radio::class, RadioAbstractFieldDto::class),
            'select' => new FieldMap(Select::class, SelectAbstractFieldDto::class),
            'textarea' => new FieldMap(Textarea::class, TextareaAbstractFieldDto::class),
            'url' => new FieldMap(Url::class, UrlAbstractFieldDto::class),
            'uploader' => new FieldMap(Uploader::class, UploaderAbstractFieldDto::class),
            'key_value_editor' => new FieldMap(KeyValueEditor::class, KeyValueEditorAbstractFieldDto::class),
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