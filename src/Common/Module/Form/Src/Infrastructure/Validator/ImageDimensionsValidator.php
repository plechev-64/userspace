<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Validator;

use UserSpace\Common\Module\Form\Src\Domain\FileValidatorInterface;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

class ImageDimensionsValidator implements FileValidatorInterface
{

    private ?int $minWidth;
    private ?int $minHeight;
    private ?int $maxWidth;
    private ?int $maxHeight;

    public function __construct(?int $minWidth = null, ?int $minHeight = null, ?int $maxWidth = null, ?int $maxHeight = null)
    {
        $this->minWidth = $minWidth;
        $this->minHeight = $minHeight;
        $this->maxWidth = $maxWidth;
        $this->maxHeight = $maxHeight;
    }

    /**
     * Валидирует файл из массива $_FILES.
     *
     * @param array $file
     * @return string|null
     */
    public function validate(array $file): ?string
    {
        $imageSize = getimagesize($file['tmp_name']);
        if (!$imageSize) {
            return __('Could not determine image size.', 'usp');
        }

        [$width, $height] = $imageSize;

        if ($this->minWidth && $width < $this->minWidth) {
            return sprintf(__('Image width is too small. Minimum width is %dpx.', 'usp'), $this->minWidth);
        }
        if ($this->minHeight && $height < $this->minHeight) {
            return sprintf(__('Image height is too small. Minimum height is %dpx.', 'usp'), $this->minHeight);
        }
        if ($this->maxWidth && $width > $this->maxWidth) {
            return sprintf(__('Image width is too large. Maximum width is %dpx.', 'usp'), $this->maxWidth);
        }
        if ($this->maxHeight && $height > $this->maxHeight) {
            return sprintf(__('Image height is too large. Maximum height is %dpx.', 'usp'), $this->maxHeight);
        }
        return null;
    }

    public function getMinWidth(): ?int
    {
        return $this->minWidth;
    }

    public function getMinHeight(): ?int
    {
        return $this->minHeight;
    }

    public function getMaxWidth(): ?int
    {
        return $this->maxWidth;
    }

    public function getMaxHeight(): ?int
    {
        return $this->maxHeight;
    }
}