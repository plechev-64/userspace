<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure;

use InvalidArgumentException;
use UserSpace\Common\Module\Form\Src\Domain\Field\DTO\AbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Domain\Field\FieldInterface;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\Boolean;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\Checkbox;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\Date;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\BooleanAbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\CheckboxAbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\DateAbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\KeyValueEditorAbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\RadioAbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\SelectAbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\TextareaAbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\TextAbstractFieldDto;
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
class FieldMapper
{

    /**
     * @var array<string, array{class: class-string<FieldInterface>, dto: class-string<AbstractFieldDto>}>
     */
    private array $map;

    public function __construct()
    {
        $this->map = [
            'boolean' => ['class' => Boolean::class, 'dto' => BooleanAbstractFieldDto::class],
            'text' => ['class' => Text::class, 'dto' => TextAbstractFieldDto::class],
            'checkbox' => ['class' => Checkbox::class, 'dto' => CheckboxAbstractFieldDto::class],
            'date' => ['class' => Date::class, 'dto' => DateAbstractFieldDto::class],
            'radio' => ['class' => Radio::class, 'dto' => RadioAbstractFieldDto::class],
            'select' => ['class' => Select::class, 'dto' => SelectAbstractFieldDto::class],
            'textarea' => ['class' => Textarea::class, 'dto' => TextareaAbstractFieldDto::class],
            'url' => ['class' => Url::class, 'dto' => UrlAbstractFieldDto::class],
            'uploader' => ['class' => Uploader::class, 'dto' => UploaderAbstractFieldDto::class],
            'key_value_editor' => ['class' => KeyValueEditor::class, 'dto' => KeyValueEditorAbstractFieldDto::class],
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
     * @return class-string<AbstractFieldDto>
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
     * @return array<string, array{class: class-string<FieldInterface>, dto: class-string<AbstractFieldDto>}>
     */
    public function getMap(): array
    {
        return $this->map;
    }
}