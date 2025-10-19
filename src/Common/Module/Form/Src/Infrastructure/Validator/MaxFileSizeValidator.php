<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Validator;

use UserSpace\Common\Module\Form\Src\Domain\FileValidatorInterface;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

class MaxFileSizeValidator implements FileValidatorInterface
{

    private float $maxSizeMb;

    public function __construct(float $maxSizeMb)
    {
        $this->maxSizeMb = $maxSizeMb;
    }

    /**
     * Валидирует файл из массива $_FILES.
     *
     * @param array $file
     * @return string|null
     */
    public function validate(array $file): ?string
    {
        if (($file['size'] / 1024 / 1024) > $this->maxSizeMb) {
            return sprintf(__('File is too large. Maximum size is %s MB.', 'usp'), $this->maxSizeMb);
        }
        return null;
    }

    public function getMaxSizeMb(): float
    {
        return $this->maxSizeMb;
    }
}