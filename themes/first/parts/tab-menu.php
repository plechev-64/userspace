<?php
/**
 * Шаблон для рендеринга меню вкладок.
 *
 * @var array $tabs_to_render Массив объектов AbstractTab для рендеринга.
 * @var bool  $activate_first Должен ли первый элемент быть активным.
 */

if ( ! defined('ABSPATH') || empty($tabs_to_render)) {
	return;
}

$is_first = $activate_first ?? false;

foreach ($tabs_to_render as $tab) :
	$active_class = $is_first ? 'active' : '';
	$is_first     = false; // Активируем только первый элемент

	$has_subtabs    = ! empty($tab->getSubTabs());
	$parent_classes = $has_subtabs ? 'has-submenu' : '';

	// Если это родительская вкладка с подменю, ссылка не должна вести на контент,
	// а только служить для раскрытия меню. JS обработает клик и перейдет на первую дочернюю.
	$link_href    = $has_subtabs ? '#' : '#' . esc_attr($tab->getId());
	$data_tab_id  = $has_subtabs ? '' : 'data-tab-id="' . esc_attr($tab->getId()) . '"';
	?>
	<div class="usp-account-menu-item <?php echo esc_attr($parent_classes); ?>">
		<a href="<?php echo $link_href; ?>" class="<?php echo esc_attr($active_class); ?>" <?php echo $data_tab_id; ?>>
			<?php if ($tab->getIcon()) : ?>
				<span class="dashicons <?php echo esc_attr($tab->getIcon()); ?>"></span>
			<?php endif; ?>
			<span class="usp-menu-item-title"><?php echo esc_html($tab->getTitle()); ?></span>
		</a>

		<?php if ($has_subtabs) : ?>
			<div class="usp-account-submenu">
				<?php foreach ($tab->getSubTabs() as $sub_tab) : ?>
					<div class="usp-account-menu-item">
						<a href="#<?php echo esc_attr($sub_tab->getId()); ?>" data-tab-id="<?php echo esc_attr($sub_tab->getId()); ?>">
							<span class="usp-menu-item-title"><?php echo esc_html($sub_tab->getTitle()); ?></span>
						</a>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
<?php endforeach; ?>