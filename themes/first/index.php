<?php

namespace UserSpace\Themes\First;

use UserSpace\Common\Module\Tabs\Src\Infrastructure\TabManager;
use UserSpace\Common\Module\Tabs\Src\Infrastructure\TabRenderer;
use UserSpace\Common\Service\TemplateManagerInterface;
use UserSpace\Common\Service\AvatarManager;
use UserSpace\Common\Service\ViewedUserContext;
use UserSpace\Core\Asset\AssetRegistryInterface;
use UserSpace\Core\ContainerInterface;
use UserSpace\Core\Hooks\HookManagerInterface;
use UserSpace\Core\String\StringFilterInterface;
use UserSpace\Core\Theme\ThemeInterface;
use UserSpace\Core\Theme\ThemeManager;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Класс, представляющий тему "First".
 * Реализует ThemeInterface для интеграции с ядром плагина UserSpace.
 */
class FirstTheme implements ThemeInterface
{
    private HookManagerInterface $hookManager;
    private AssetRegistryInterface $assetRegistry;
    private StringFilterInterface $stringFilter;
    private ViewedUserContext $viewedUserContext;
    private AvatarManager $avatarManager;
    private TabRenderer $tabRenderer;
    private TabManager $tabManager;
    private TemplateManagerInterface $templateManager;

    /**
     * @inheritDoc
     */
    public function setup(ContainerInterface $container): void
    {
        $this->hookManager = $container->get(HookManagerInterface::class);
        $this->assetRegistry = $container->get(AssetRegistryInterface::class);
        $this->stringFilter = $container->get(StringFilterInterface::class);
        $this->viewedUserContext = $container->get(ViewedUserContext::class);
        $this->avatarManager = $container->get(AvatarManager::class);
        $this->tabRenderer = $container->get(TabRenderer::class);
        $this->tabManager = $container->get(TabManager::class);
        $this->templateManager = $container->get(TemplateManagerInterface::class);

        $this->hookManager->addAction('wp_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    public function enqueueAssets(): void
    {
        $themeUrl = USERSPACE_PLUGIN_URL . 'themes/first/';
        $this->assetRegistry->enqueueStyle('usp-account-template-first-style', $themeUrl . 'style.css', [], USERSPACE_VERSION);
        $this->assetRegistry->enqueueScript('usp-account-template-first-js', $themeUrl . 'main.js', ['usp-core'], USERSPACE_VERSION, true);

        $this->assetRegistry->localizeScript('usp-account-template-first-js', 'uspL10n', [
            'loading'   => $this->stringFilter->translate('Loading...', 'usp'),
            'loadError' => $this->stringFilter->translate('Failed to load content.', 'usp'),
        ]);
    }

    public function prepareTemplateData(): array
    {
        return [
            'avatarBlock' => $this->avatarManager->renderAvatarBlock(),
            'headerMenu' => $this->renderMenu('header', false),
            'sidebarMenu' => $this->renderMenu('sidebar', true),
            'tabsContent' => $this->tabRenderer->renderTabsContent(
                '<div class="usp-account-tab-pane %4$s" id="%1$s" data-content-type="%2$s" data-content-source="%3$s">%5$s</div>',
                'sidebar'
            ),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'First';
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
        return $this->getPath() . '/template.php';
    }

    /**
     * Рендерит меню вкладок для указанной локации.
     *
     * @param string $location Идентификатор локации ('header', 'sidebar', etc.).
     * @param bool $activate_first Сделать ли первую вкладку в меню активной.
     *
     * @return string Сгенерированный HTML-код меню.
     */
    private function renderMenu(string $location, bool $activate_first = false): string
    {
        $tabs_to_render = $this->tabManager->getTabs($location);

        if (empty($tabs_to_render)) {
            return '';
        }

        return $this->templateManager->render('tab_menu', [
            'tabs_to_render' => $tabs_to_render,
            'activate_first' => $activate_first,
            'location' => $location,
        ]);
    }
}

/**
 * @var ThemeManager $themeRegistry Объект регистратора, переданный из ThemeManager.
 */
if (isset($themeRegistry) && $themeRegistry instanceof ThemeManager) {
    return $themeRegistry->register(FirstTheme::class);
}