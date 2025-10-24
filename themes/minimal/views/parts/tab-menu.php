<?php
/**
 * Шаблон для рендеринга меню личного кабинета (вкладки и кнопки).
 *
 * @var \UserSpace\Common\Module\Tabs\Src\Domain\ItemInterface[] $items_to_render Массив объектов для рендеринга.
 * @var string|null $active_tab_id ID активной вкладки.
 */

use UserSpace\Common\Module\Tabs\Src\Domain\AbstractTab;

if (!defined('ABSPATH') || empty($items_to_render)) {
    return;
}

foreach ($items_to_render as $item) :
    $is_tab = $item instanceof AbstractTab;

    if ($is_tab) {
        $has_subtabs = !empty($item->getSubTabs());
        $parent_classes = $has_subtabs ? 'has-submenu' : '';

        // Определяем, активна ли текущая вкладка или одна из ее дочерних вкладок
        $is_parent_active = $has_subtabs && in_array($active_tab_id, array_map(fn($sub) => $sub->getId(), $item->getSubTabs()));
        $active_class = ($item->getId() === $active_tab_id || $is_parent_active) ? 'active' : '';

        // Если это родительская вкладка с подменю, ссылка не должна вести на контент,
        // а только служить для раскрытия меню. JS обработает клик и перейдет на первую дочернюю.
        $link_href = $has_subtabs ? '#' : '#' . esc_attr($item->getId());
        $data_tab_id = $has_subtabs ? '' : 'data-tab-id="' . esc_attr($item->getId()) . '"';
    }
    ?>
    <div class="usp-account-menu-item <?php if ($is_tab) echo esc_attr($parent_classes); ?>">
        <?php if ($is_tab) : ?>
            <a href="<?php echo $link_href; ?>"
               class="<?php echo esc_attr($active_class); ?>" <?php echo $data_tab_id; ?>>
                <?php if ($item->getIcon()) : ?>
                    <span class="dashicons <?php echo esc_attr($item->getIcon()); ?>"></span>
                <?php endif; ?>
                <span class="usp-menu-item-title"><?php echo esc_html($item->getTitle()); ?></span>
            </a>
        <?php else : // Это кнопка ?>
            <button type="button" class="usp-account-menu-button"
                    data-action-endpoint="<?php echo esc_attr($item->getActionEndpoint()); ?>">
                <?php if ($item->getIcon()) : ?>
                    <span class="dashicons <?php echo esc_attr($item->getIcon()); ?>"></span>
                <?php endif; ?>
                <span class="usp-menu-item-title"><?php echo esc_html($item->getTitle()); ?></span>
            </button>
        <?php endif; ?>

        <?php if ($is_tab && $has_subtabs) : ?>
            <div class="usp-account-submenu">
                <?php foreach ($item->getSubTabs() as $sub_tab) : ?>
                    <?php $sub_active_class = ($sub_tab->getId() === $active_tab_id) ? 'active' : ''; ?>
                    <div class="usp-account-menu-item">
                        <a href="#<?php echo esc_attr($sub_tab->getId()); ?>"
                           class="<?php echo esc_attr($sub_active_class); ?>"
                           data-tab-id="<?php echo esc_attr($sub_tab->getId()); ?>">
                            <span class="usp-menu-item-title"><?php echo esc_html($sub_tab->getTitle()); ?></span>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endforeach; ?>