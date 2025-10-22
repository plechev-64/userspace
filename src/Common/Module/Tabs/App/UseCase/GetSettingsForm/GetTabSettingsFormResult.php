<?php

namespace UserSpace\Common\Module\Tabs\App\UseCase\GetSettingsForm;

/**
 * Результат успешного получения формы настроек вкладки.
 */
class GetTabSettingsFormResult
{
    public function __construct(
        public readonly string $html
    ) {
    }
}