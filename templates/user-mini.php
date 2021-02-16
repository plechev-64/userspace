<?php
global $usp_user, $usp_users_set;
?>
<div class="usp-user usps" data-user-id="<?php echo $usp_user->ID; ?>">
    <a class="usp-user__ava usps usps__relative" title="<?php usp_user_name(); ?>" href="<?php usp_user_url(); ?>">
        <?php usp_user_avatar( 50 ); ?>
        <?php usp_user_action(); ?>
    </a>
</div>