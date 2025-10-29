<?php

namespace UserSpace\Common\Module\Form\Src\Domain\Service;

use UserSpace\Common\Module\Form\Src\Domain\Form\Config\FormConfig;
use UserSpace\Core\Sanitizer\ClearedDataInterface;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Интерфейс для сервиса, который очищает данные формы на основе ее конфигурации.
 */
interface FormSanitizerInterface
{
    /**
     * @param FormConfig $formConfig Конфигурация формы.
     * @param array<string, mixed> $requestData Входящие данные запроса.
     */
    public function sanitize(FormConfig $formConfig, array $requestData): ClearedDataInterface;
}