<?php

namespace UserSpace\Common\Module\Tabs\App\UseCase\UpdateConfig;

use UserSpace\Common\Module\Tabs\Src\Infrastructure\TabConfigManager;
use UserSpace\Core\Exception\UspException;
use UserSpace\Core\String\StringFilterInterface;

class UpdateTabsConfigUseCase
{
    public function __construct(
        private readonly TabConfigManager      $tabConfigManager,
        private readonly StringFilterInterface $str
    )
    {
    }

    /**
     * @throws UspException
     */
    public function execute(UpdateTabsConfigCommand $command): void
    {
        /** @todo передавать через команду понятные параметры */
        $config = json_decode($command->configJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new UspException($this->str->translate('Invalid JSON format.'), 400);
        }

        $this->tabConfigManager->save($config);

    }
}