<?php

namespace UserSpace\Common\Module\Locations\App\UseCase\GetContent;

use UserSpace\Common\Module\Locations\Src\Infrastructure\ItemManager;
use UserSpace\Common\Module\Locations\Src\Infrastructure\ItemRenderer;
use UserSpace\Core\Asset\AssetRegistryInterface;
use UserSpace\Core\Exception\UspException;
use UserSpace\Core\String\StringFilterInterface;

class GetTabContentUseCase
{
    public function __construct(
        private readonly ItemManager            $tabManager,
        private readonly ItemRenderer           $tabRenderer,
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

        $foundTab = $this->tabManager->getItem($command->tabId);

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