<?php
/**
 * @var object $item The user data item.
 */

$displayName = !empty(trim($item->first_name . ' ' . $item->last_name))
    ? trim($item->first_name . ' ' . $item->last_name)
    : $item->display_name;
?>
<div class="usp-grid-item user-item">
    <div class="user-item__avatar">
        <?= get_avatar($item->id, 64) ?>
    </div>
    <div class="user-item__info">
        <h3 class="user-item__name">
            <a href="<?= esc_url($item->profile_url ?? '#') ?>"><?= esc_html($displayName) ?></a>
        </h3>
        <p class="user-item__login">@<?= esc_html($item->login) ?></p>
        <?php if (!empty($item->email)): ?>
            <p class="user-item__email">
                <a href="mailto:<?= esc_attr($item->email) ?>"><?= esc_html($item->email) ?></a>
            </p>
        <?php endif; ?>
    </div>
</div>