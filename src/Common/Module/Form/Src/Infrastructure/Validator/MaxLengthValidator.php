<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Validator;

use UserSpace\Common\Module\Form\Src\Domain\Field\FieldInterface;
use UserSpace\Common\Module\Form\Src\Domain\ValidatorInterface;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

class MaxLengthValidator implements ValidatorInterface
{

    private int $maxLength;

    public function __construct(int $maxLength)
    {
        $this->maxLength = $maxLength;
    }

    public function validate(FieldInterface $field): ?string
    {
        $value = (string)$field->getValue();
        if (!empty($value) && mb_strlen($value) > $this->maxLength) {
            return sprintf(__('Field "%s" must be no more than %d characters long.', 'usp'), $field->getLabel(), $this->maxLength);
        }

        return null;
    }
}