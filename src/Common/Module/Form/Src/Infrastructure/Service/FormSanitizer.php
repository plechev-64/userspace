<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Service;

use UserSpace\Common\Module\Form\Src\Domain\Field\FieldInterface;
use UserSpace\Common\Module\Form\Src\Domain\Form\Config\FormConfig;
use UserSpace\Common\Module\Form\Src\Domain\Service\FieldMapRegistryInterface;
use UserSpace\Common\Module\Form\Src\Domain\Service\FormSanitizerInterface;
use UserSpace\Core\Sanitizer\ClearedDataInterface;
use UserSpace\Core\Sanitizer\SanitizerInterface;

if (!defined('ABSPATH')) {
    exit;
}

class FormSanitizer implements FormSanitizerInterface
{
    public function __construct(
        private readonly FieldMapRegistryInterface $fieldMapRegistry,
        private readonly SanitizerInterface        $sanitizer
    )
    {
    }

    public function sanitize(FormConfig $formConfig, array $requestData): ClearedDataInterface
    {
        $sanitizationConfig = [];
        $fields = $formConfig->getFields();

        foreach ($fields as $fieldName => $fieldConfig) {
            /** @var class-string<FieldInterface> $fieldClassName */
            $fieldClassName = $this->fieldMapRegistry->getClass($fieldConfig['type']);
            $sanitizationConfig[$fieldName] = $fieldClassName::getSanitizationRule();
        }

        return $this->sanitizer->sanitize($requestData, $sanitizationConfig);
    }
}