<?php
// Защита от прямого доступа к файлу

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @var string $headerMenu
 * @var string $sidebarMenu
 * @var string $mainContent
 * @var string $avatarBlock
 */
?>
<div class="usp-account-layout">
    <header class="usp-account-layout__header">
        <button class="usp-mobile-menu-toggle" aria-label="Открыть меню" aria-expanded="false">
            <span class="usp-mobile-menu-toggle__icon"></span>
        </button>
        <div class="usp-account-layout__header-title">
            <h1>Личный кабинет</h1>
        </div>
        <nav class="usp-header-nav">
            <?php echo $headerMenu; ?>
        </nav>
    </header>
    <div class="usp-account-layout__body">
        <aside class="usp-account-layout__sidebar">
            <?php echo $avatarBlock; ?>
            <nav class="usp-sidebar-nav">
                <?php echo $sidebarMenu; ?>
            </nav>
        </aside>
        <main class="usp-account-layout__main">
            <div class="usp-content-area">
                <?php echo $mainContent; ?>
            </div>
        </main>
    </div>
</div>