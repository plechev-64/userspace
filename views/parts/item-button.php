<?php
// Защита от прямого доступа к файлу
use UserSpace\Common\Module\Locations\Src\Domain\ItemInterface;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * @var ItemInterface $item
 * @var string $title
 * @var string $actionEndpoint
 */
?>
<a class="usp-sidebar-nav__link usp-sidebar-nav__link--action" href="#" data-action-endpoint="<?php echo esc_url($actionEndpoint); ?>" role="button">
     <span class="usp-sidebar-nav__icon">
 		<?php if ( $item->getIcon() ) : ?>
            <i class="uspi <?php echo esc_attr( $item->getIcon() ); ?>"></i>
        <?php endif; ?>
     </span>
    <span class="usp-sidebar-nav__title">
         <?php echo esc_html($title); ?>
     </span>
</a>