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
?>
<div class="usp-account-wrapper usp-theme-minimal">
    <div class="usp-account-sidebar">
        <?php echo $avatarBlock; ?>
        <div class="usp-account-menu">
            <?php echo $sidebarMenu; ?>
        </div>
    </div>
    <div class="usp-account-content">
        <?php echo $tabsContent; ?>
    </div>
</div>