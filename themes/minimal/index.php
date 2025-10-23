<?php

namespace UserSpace\Theme\Minimal;

use UserSpace\Core\Asset\AssetRegistryInterface;
use UserSpace\Core\Container\ContainerInterface;
use UserSpace\Core\Hooks\HookManagerInterface;
use UserSpace\Core\String\StringFilterInterface;
use UserSpace\Core\Theme\ThemeInterface;
use UserSpace\Core\Theme\ThemeManagerInterface;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Класс, представляющий тему "Minimal".
 * Реализует ThemeInterface для интеграции с ядром плагина UserSpace.
 */
class MinimalTheme implements ThemeInterface
{
    private HookManagerInterface $hookManager;
    private AssetRegistryInterface $assetRegistry;
    private StringFilterInterface $stringFilter;
    private TabLocationService $tabLocationService;
    private ViewDataProvider $viewDataProvider;

    /**
     * @inheritDoc
     */
    public function setup(ContainerInterface $container): void
    {
        $this->hookManager = $container->get(HookManagerInterface::class);
        $this->assetRegistry = $container->get(AssetRegistryInterface::class);
        $this->stringFilter = $container->get(StringFilterInterface::class);
        $this->tabLocationService = $container->get(TabLocationService::class);
        $this->viewDataProvider = $container->get(ViewDataProvider::class);
        $this->tabLocationService->registerThemeLocations();
        $this->hookManager->addAction('wp_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    public function enqueueAssets(): void
    {
        $themeUrl = USERSPACE_PLUGIN_URL . 'themes/minimal/';
        $this->assetRegistry->enqueueStyle('usp-account-template-minimal-style', $themeUrl . 'assets/style.css', [], USERSPACE_VERSION);
        $this->assetRegistry->enqueueScript('usp-account-template-minimal-js', $themeUrl . 'assets/main.js', ['usp-core'], USERSPACE_VERSION, true);

        $this->assetRegistry->localizeScript('usp-account-template-minimal-js', 'uspL10n', [
            'loading' => $this->stringFilter->translate('Loading...', 'usp'),
            'loadError' => $this->stringFilter->translate('Failed to load content.', 'usp'),
        ]);
    }

    public function prepareTemplateData(): array
    {
        return $this->viewDataProvider->prepareTemplateData();
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'Minimal';
    }

    /**
     * @inheritDoc
     */
    public function getPath(): string
    {
        return __DIR__;
    }

    /**
     * @inheritDoc
     */
    public function getContainerConfigPath(): ?string
    {
        return $this->getPath() . '/config/container.php';
    }

    /**
     * @inheritDoc
     */
    public function getTemplatePath(): ?string
    {
        return $this->getPath() . '/views/template.php';
    }
}

/**
 * @var ThemeManagerInterface $themeRegistry Объект регистратора, переданный из ThemeManagerInterface.
 */
if (isset($themeRegistry) && $themeRegistry instanceof ThemeManagerInterface) {
    return $themeRegistry->register(MinimalTheme::class);
}