<?php
/**
 * Html template for Notices
 *
 * This template can be overridden by copying it to yourtheme/userspace/templates/usp-notice.php
 * or from a special plugin directory wp-content/userspace/templates/usp-notice.php
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
 * @var object $notice id of user
 */
defined( 'ABSPATH' ) || exit;
?>

<div class="<?php echo wp_kses( $notice->class, 'strip' ); ?>">

	<?php if ( ! empty( $notice->icon ) ) : ?>
        <i class="uspi <?php echo sanitize_html_class( $notice->icon ); ?> usp-notice__ico" aria-hidden="true"></i>
	<?php endif; ?>

	<?php if ( ! empty( $notice->cookie ) ) : ?>
        <i class="uspi fa-times usp-notice__close" aria-hidden="true" data-notice_id="<?php echo esc_html( $notice->cookie ); ?>"
           data-notice_time="<?php echo absint( $notice->cookie_time ); ?>" onclick="usp_close_notice(this);return false;"></i>
	<?php endif; ?>

	<?php if ( ! empty( $notice->title ) ) : ?>
        <div class="usp-notice__title"><?php echo esc_html( $notice->title ); ?></div>
	<?php endif; ?>

    <div class="usp-notice__text"><?php echo $notice->text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
</div>
