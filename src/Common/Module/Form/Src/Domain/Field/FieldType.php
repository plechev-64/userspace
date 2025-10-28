<?php

namespace UserSpace\Common\Module\Form\Src\Domain\Field;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Перечисление всех доступных типов полей в системе.
 */
enum FieldType: string
{
    case BOOLEAN = 'boolean';
    case TEXT = 'text';
    case CHECKBOX = 'checkbox';
    case DATE = 'date';
    case RADIO = 'radio';
    case SELECT = 'select';
    case TEXTAREA = 'textarea';
    case URL = 'url';
    case UPLOADER = 'uploader';
    case KEY_VALUE_EDITOR = 'key_value_editor';
    case NUMBER = 'number';
    case PASSWORD = 'password';
    case EMAIL = 'email';
}