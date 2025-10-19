<?php
/**
 * Шаблон личного кабинета для темы "First".
 */

use UserSpace\Common\Module\Tabs\Src\Infrastructure\TabManager;
use UserSpace\Common\Module\Tabs\Src\Infrastructure\TabRenderer;
use UserSpace\Common\Service\AvatarManager;
use UserSpace\Common\Service\ViewedUserContext;
use UserSpace\Plugin;

if (!defined('ABSPATH')) {
    exit;
}

// 1. Получаем необходимые сервисы из контейнера
$pluginContainer = Plugin::getInstance()->getContainer();
$viewedUserContext = $pluginContainer->get(ViewedUserContext::class);
$avatarManager = $pluginContainer->get(AvatarManager::class);
$tabRenderer = $pluginContainer->get(TabRenderer::class);

// 2. Получаем данные для шаблона
$viewedUser = $viewedUserContext->getViewedUser();

if (!$viewedUser) {
    return;
}

// 3. Используем сервисы темы для подготовки данных
$tabManager = $pluginContainer->get(TabManager::class);

// 4. Подключаем стили и скрипты
$theme_url = plugin_dir_url(__FILE__);
wp_enqueue_style(
    'usp-account-template-first-style',
    $theme_url . 'style.css',
    [],
    USERSPACE_VERSION
);
wp_enqueue_script(
    'usp-account-template-first-js',
    $theme_url . 'main.js',
    ['usp-core'],
    USERSPACE_VERSION,
    true
);

wp_localize_script(
    'usp-account-template-first-js',
    'uspL10n',
    [
        'loading'   => __('Loading...', 'usp'),
        'loadError' => __('Failed to load content.', 'usp'),
    ]
);

?>
<div class="usp-account-wrapper">
    <div class="usp-account-header">
        <?php echo $avatarManager->renderAvatarBlock(); ?>
        <div class="usp-header-tabs">
            <div class="usp-account-menu">
				<?php // Выводим меню для 'header', не активируя первый элемент
				echo $tabRenderer->renderMenu('header', false); ?>
            </div>
        </div>
    </div>

    <div class="usp-account-sidebar">
        <div class="usp-account-menu">
			<?php // Выводим меню для 'sidebar', активируя первый элемент
			echo $tabRenderer->renderMenu('sidebar', true); ?>
        </div>
    </div>
    <div class="usp-account-content">
        <?php
        echo $tabRenderer->renderTabsContent(
            '<div class="usp-account-tab-pane %4$s" id="%1$s" data-content-type="%2$s" data-content-source="%3$s">%5$s</div>',
            'sidebar' // Указываем, что 'sidebar' - это основная локация для определения активной вкладки
        );
        ?>
    </div>
</div>