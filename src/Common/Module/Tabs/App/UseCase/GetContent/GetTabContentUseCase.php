<?php

namespace UserSpace\Common\Module\Tabs\App\UseCase\GetContent;

use UserSpace\Common\Module\Tabs\Src\Infrastructure\TabManager;
use UserSpace\Common\Module\Tabs\Src\Infrastructure\TabRenderer;
use UserSpace\Core\Asset\AssetRegistryInterface;
use UserSpace\Core\Exception\UspException;
use UserSpace\Core\String\StringFilterInterface;

class GetTabContentUseCase
{
    public function __construct(
        private readonly TabManager             $tabManager,
        private readonly TabRenderer            $tabRenderer,
        private readonly AssetRegistryInterface $assetRegistry,
        private readonly StringFilterInterface  $str
    )
    {
    }

    /**
     * @throws UspException
     */
    public function execute(GetTabContentCommand $command): GetTabContentResult
    {
        if (empty($command->tabId)) {
            throw new UspException($this->str->translate('Tab ID is missing.'), 400);
        }

        $foundTab = $this->tabManager->getTab($command->tabId);

        if (!$foundTab) {
            throw new UspException($this->str->translate('Tab not found.'), 404);
        }

        // Очищаем очереди скриптов и стилей перед генерацией контента
        $this->assetRegistry->clear();

        $html = $this->tabRenderer->render($foundTab);

        $assets = $this->assetRegistry->getAssets();

        return new GetTabContentResult($html, $assets);
    }
}