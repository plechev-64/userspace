<?php
/**
 * Html template for displaying the user's card
 *
 * This template can be overridden by copying it to yourtheme/userspace/templates/user-mini.php
 * or from a special plugin directory wp-content/userspace/templates/user-mini.php
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
 * @var USP_User $user
 * @var array $custom_data - data to display
 */

defined( 'ABSPATH' ) || exit;

$args = [
	'parent_class' => 'usp-user__ava usps usps__relative',
	'parent_title' => $user->get_username(),
];
?>

<div class="usp-user usps usps__mb-6 usps__mr-6" data-user-id="<?php echo intval( $user->ID ); ?>">
	<?php echo $user->get_avatar( 50, $user->get_url(), $args, $user->get_action( 'icon' ) );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</div>
