<?php
// Защита от прямого доступа к файлу
use UserSpace\Common\Module\Locations\Src\Domain\ItemInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Шаблон для одного элемента-вкладки в меню.
 *
 * @var ItemInterface $item
 * @var string $url
 * @var string $title
 * @var bool $is_active
 */
$active_class  = $is_active ? 'is-active' : '';
?>

<a class="usp-sidebar-nav__link <?php echo esc_attr( $active_class ); ?>" href="<?php echo esc_url( $url ); ?>">
    <span class="usp-sidebar-nav__icon">
 		<?php if ( $item->getIcon() ) : ?>
            <i class="uspi <?php echo esc_attr( $item->getIcon() ); ?>"></i>
        <?php endif; ?>
     </span>
    <span class="usp-sidebar-nav__title">
        <?php echo esc_html( $title ); ?>
    </span>
</a>