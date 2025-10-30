<?php
/**
 * Шаблон личного кабинета для темы "Minimal".
 *
 * @var string $avatarBlock HTML-блок с аватаром пользователя.
 * @var string $sidebarMenu HTML-меню для сайдбара.
 * @var string $tabsContent HTML-контент вкладок.
 */

if (!defined('ABSPATH')) {
    exit;
}
/**
 * @var string $sidebarMenu
 * @var string $mainContent
 * @var string $avatarBlock
 */
?>
<div class="usp-account-layout usp-account-layout--minimal">
    <header class="usp-account-layout__header">
        <div class="usp-header-left">
            <nav class="usp-main-nav">
                <?php echo $sidebarMenu; // Основное меню теперь здесь ?>
            </nav>
        </div>
        <div class="usp-header-right">
            <?php echo $avatarBlock; ?>
            <button class="usp-mobile-menu-toggle" aria-label="Открыть меню" aria-expanded="false">
                <span class="usp-mobile-menu-toggle__icon"></span>
            </button>
        </div>
    </header>

    <main class="usp-account-layout__main">
        <div class="usp-content-area">
            <?php echo $mainContent; ?>
        </div>
    </main>
</div>