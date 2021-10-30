<?php
/**
 * Html template for a single statistic element
 *
 * This template can be overridden by copying it to yourtheme/userspace/templates/usp-statistics-item.php
 * or from a special plugin directory wp-content/userspace/templates/usp-statistics-item.php
 *
 * HOWEVER, on occasion UserSpace will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.user-space.com/document/template-structure/
 *
 * @version 1.0.0
 *
 * @var string $class additional class
 * @var string $icon fa-icon
 * @var string $title statistics title
 * @var int $count statistics counter or date
 */
defined( 'ABSPATH' ) || exit;
?>

<span title="<?php echo esc_html( $title ); ?>"
      class="usp-meta-item <?php echo sanitize_html_class( $class ) ?> usps usps__nowrap usps__ai-center usps__line-1">
    <i class="usp-meta-ico uspi <?php echo sanitize_html_class( $icon ) ?>" aria-hidden="true"></i>
    <span><?php echo esc_html( $count ) ?></span>
</span>
