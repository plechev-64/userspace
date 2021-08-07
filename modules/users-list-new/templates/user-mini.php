<?php

/**
 * @var USP_User $user
 * @var array $custom_data - data to display
 */

$args = [
	'parent_class' => 'usp-user__ava usps usps__relative',
	'parent_title' => $user->get_username(),
];
?>
<div class="usp-user usps" data-user-id="<?php echo $user->ID; ?>">
	<?php echo $user->get_avatar( 50, $user->get_url(), $args, $user->get_action( 'icon' ) ); ?>
</div>
