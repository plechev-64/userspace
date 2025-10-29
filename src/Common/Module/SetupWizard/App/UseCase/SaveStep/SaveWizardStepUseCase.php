<?php

namespace UserSpace\Common\Module\SetupWizard\App\UseCase\SaveStep;

use UserSpace\Common\Module\Settings\Src\Domain\PluginSettingsInterface;
use UserSpace\Core\Exception\UspException;
use UserSpace\Core\String\StringFilterInterface;

class SaveWizardStepUseCase
{
    public function __construct(
        private readonly StringFilterInterface $str,
        private readonly PluginSettingsInterface $pluginSettings
    )
    {
    }

    /**
     * @throws UspException
     */
    public function execute(SaveWizardStepCommand $command): void
    {
        $sanitizedData = $command->stepData;

        if (empty($sanitizedData) || !is_array($sanitizedData)) {
            throw new UspException($this->str->translate('No data to save.'), 400);
        }

        // Получаем текущие настройки и объединяем их с новыми, уже очищенными данными
        $currentOptions = $this->pluginSettings->all();
        $newOptions = array_merge($currentOptions, $sanitizedData);
        $this->pluginSettings->updateAll($newOptions);
    }
}