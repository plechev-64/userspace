<?php
global $usp_user, $usp_users_set;

$args = [
	'parent_class' => 'usp-user__ava usps usps__relative',
	'parent_title' => usp_user_get_username(),
];
?>
<div class="usp-user usps" data-user-id="<?php echo $usp_user->ID; ?>">
	<?php echo usp_get_avatar( $usp_user->ID, 50, usp_user_get_url( $usp_user->ID ), $args, usp_get_user_action() ); ?>
</div>
