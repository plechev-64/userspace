<?php
/**
 * Шаблон личного кабинета (Тема "Second").
 * @package UserSpace
 *
 * @var \UserSpace\Core\Tabs\TabDto[] $tabs
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="usp-account-wrapper-second">
    <div class="usp-account-tabs-nav">
        <ul class="usp-account-menu">
            <?php
            $is_first_link = true;
            foreach ($tabs as $tab) :
                // Если у родительской вкладки нет своего контента, ссылка ведет на первую дочернюю вкладку.
                $parent_href = empty($tab->contentSource) && !empty($tab->subTabs)
                    ? '#' . esc_attr($tab->subTabs[0]->id)
                    : '#' . esc_attr($tab->id);

                $has_submenu_class = !empty($tab->subTabs) ? 'has-submenu' : '';
                ?>
                <li class="usp-account-menu-item <?php echo $has_submenu_class; ?>">
                    <a href="<?php echo $parent_href; ?>"><?php echo esc_html($tab->title); ?></a>
                    <?php if (!empty($tab->subTabs)) : ?>
                        <ul class="usp-account-submenu">
                            <?php foreach ($tab->subTabs as $subTab) : ?>
                                <li class="usp-account-submenu-item">
                                    <a href="#<?php echo esc_attr($subTab->id); ?>"><?php echo esc_html($subTab->title); ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="usp-account-content">
        <?php
        $is_first_pane = true;
        foreach ($tabs as $tab) :
            // Рендерим панель для родительской вкладки, только если у нее есть контент
            if (!empty($tab->contentSource)) {
                $active_class = $is_first_pane ? 'active' : '';
                ?>
                <div class="usp-account-tab-pane <?php echo esc_attr($active_class); ?>" id="<?php echo esc_attr($tab->id); ?>">
                    <?php if (is_callable($tab->contentSource)) { echo call_user_func($tab->contentSource); } ?>
                </div>
                <?php
                if ($active_class) $is_first_pane = false;
            }

            // Рендерим панели для всех дочерних вкладок
            foreach ($tab->subTabs as $subTab) {
                $active_class = $is_first_pane ? 'active' : '';
                ?>
                <div class="usp-account-tab-pane <?php echo esc_attr($active_class); ?>" id="<?php echo esc_attr($subTab->id); ?>">
                    <?php if (is_callable($subTab->contentSource)) { echo call_user_func($subTab->contentSource); } ?>
                </div>
                <?php
                if ($active_class) $is_first_pane = false;
            }
        endforeach;
        ?>
    </div>
</div>