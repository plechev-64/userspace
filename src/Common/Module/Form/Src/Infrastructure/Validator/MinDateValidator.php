<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Validator;

use DateTime;
use Exception;
use UserSpace\Common\Module\Form\Src\Domain\Field\FieldInterface;
use UserSpace\Common\Module\Form\Src\Domain\Validator\ValidatorInterface;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

class MinDateValidator implements ValidatorInterface
{

    private string $minDate;

    public function __construct(string $minDate)
    {
        $this->minDate = $minDate;
    }

    public function validate(FieldInterface $field): ?string
    {
        if (empty($field->getValue())) {
            return null;
        }

        try {
            $valueDate = new DateTime((string)$field->getValue());
            $minDate = new DateTime($this->minDate);

            return $valueDate < $minDate ? sprintf(__('Date in "%s" field cannot be earlier than %s.', 'usp'), $field->getLabel(), $minDate->format('Y-m-d')) : null;
        } catch (Exception $e) {
            return sprintf(__('Invalid date format in "%s" field.', 'usp'), $field->getLabel());
        }
    }
}