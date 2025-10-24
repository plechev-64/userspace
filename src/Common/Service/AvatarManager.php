<?php

namespace UserSpace\Common\Service;

use UserSpace\Common\Module\Media\Src\Domain\MediaApiInterface;
use UserSpace\Common\Module\Settings\Src\Domain\OptionManagerInterface;
use UserSpace\Common\Module\User\App\Controller\UserController;
use UserSpace\Common\Module\User\Src\Domain\UserApiInterface;
use UserSpace\Common\Module\User\Src\Domain\UserInterface;
use UserSpace\Core\Hooks\HookManagerInterface;
use UserSpace\Core\SecurityHelperInterface;
use UserSpace\Core\String\StringFilterInterface;

/**
 * Управляет аватарами пользователей.
 */
class AvatarManager
{
    public function __construct(
        private readonly SecurityHelperInterface $securityHelper,
        private readonly ViewedUserContext       $viewedUserContext,
        private readonly StringFilterInterface   $str,
        private readonly OptionManagerInterface  $optionManager,
        private readonly UserApiInterface        $userApi,
        private readonly MediaApiInterface       $mediaApi,
        private readonly HookManagerInterface    $hookManager
    )
    {
    }

    /**
     * Регистрирует хуки.
     */
    public function registerHooks(): void
    {
        $this->hookManager->addFilter('pre_get_avatar_data', [$this, 'replaceAvatarData'], 10, 2);
    }

    /**
     * Получает URL аватара для указанного пользователя.
     * Учитывает кастомный аватар плагина.
     *
     * @param UserInterface|int $user Объект пользователя или его ID.
     * @return string
     */
    public function getAvatarUrl(UserInterface|int $user): string
    {
        $userId = is_numeric($user) ? $user : $user->getId();
        $customAvatarId = $this->userApi->getUserMeta($userId, UserController::AVATAR_META_KEY, true);

        if ($customAvatarId) {
            // Используем размер 'thumbnail', который обычно 150x150.
            $customAvatarUrl = $this->mediaApi->getAttachmentImageUrl((int)$customAvatarId, 'thumbnail');
            if ($customAvatarUrl) {
                return $customAvatarUrl;
            }
        }

        // Возвращаемся к стандартному аватару WordPress
        return $this->mediaApi->getAvatarUrl($userId, ['size' => 96]);
    }

    /**
     * Заменяет данные аватара по умолчанию на кастомные.
     * Хук для 'pre_get_avatar_data'.
     *
     * @param array $args Аргументы для get_avatar().
     * @param mixed $id_or_email Идентификатор пользователя.
     *
     * @return array
     */
    public function replaceAvatarData(array $args, mixed $id_or_email): array
    {
        $userId = 0;

        if (is_numeric($id_or_email)) {
            $userId = (int)$id_or_email;
        } elseif (is_object($id_or_email)) {
            if (isset($id_or_email->user_id)) {
                $userId = (int)$id_or_email->user_id;
            } elseif (isset($id_or_email->ID)) {
                $userId = (int)$id_or_email->ID;
            }
        } elseif (is_string($id_or_email) && $this->str->isEmail($id_or_email)) {
            $user = $this->userApi->getUserBy('email', $id_or_email);
            if ($user) {
                $userId = $user->ID;
            }
        }

        $avatarId = 0;
        if ($userId) {
            // Сначала ищем персональный аватар пользователя
            $avatarId = $this->userApi->getUserMeta($userId, UserController::AVATAR_META_KEY, true);
        }

        // Если персональный аватар не найден, ищем аватар по умолчанию в настройках плагина
        if (!$avatarId) {
            $options = $this->optionManager->get('usp_settings', []);
            if (!empty($options['default_avatar_id'])) {
                $avatarId = (int)$options['default_avatar_id'];
            }
        }

        if ($avatarId) {
            $size = $args['size'] ?? 96;
            $imageUrl = $this->mediaApi->getAttachmentImageUrl($avatarId, [$size, $size]);

            if ($imageUrl) {
                $args['url'] = $imageUrl;
                // Устанавливаем этот флаг, чтобы WordPress не пытался найти аватар на Gravatar
                $args['found_avatar'] = true;
            }
        }

        return $args;
    }

    /**
     * Рендерит блок для отображения и загрузки аватара.
     *
     * @return string
     */
    public function renderAvatarBlock(): string
    {
        $viewedUser = $this->viewedUserContext->getViewedUser();
        if (!$viewedUser) {
            return '';
        }

        $isOwner = $this->viewedUserContext->isOwner();
        $avatarUrl = $this->getAvatarUrl($viewedUser);

        ob_start();
        ?>
        <div class="usp-avatar-block" id="usp-avatar-block">
            <div class="usp-account-avatar-wrapper">
                <img src="<?php echo $this->str->escAttr($avatarUrl); ?>"
                     alt="<?php echo $this->str->escAttr($viewedUser->getDisplayName()); ?>"
                     class="usp-account-avatar-img">
                <?php if ($isOwner) : ?>
                    <?php
                    $config = [
                        'name' => 'user_avatar',
                        'allowedTypes' => 'image/jpeg,image/png,image/gif',
                        'maxSize' => 2, // Максимальный размер 2MB
                    ];
                    ?>
                    <div class="usp-account-avatar-uploader"
                         data-config='<?php echo $this->str->escAttr(wp_json_encode($config)); ?>'
                         data-signature="<?php echo $this->str->escAttr($this->securityHelper->sign($config)); ?>"
                    >
                        <button type="button" class="usp-change-avatar-btn">
                            <span class="dashicons dashicons-camera"></span>
                        </button>
                        <input type="file" class="usp-avatar-input" style="display: none;"
                               accept="<?php echo $this->str->escAttr($config['allowedTypes']); ?>">
                        <div class="usp-avatar-status"></div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="usp-account-user-info">
                <h2 class="usp-account-display-name"><?php echo $this->str->escHtml($viewedUser->getDisplayName()); ?></h2>
                <p class="usp-account-user-email"><?php echo $this->str->escHtml($viewedUser->getEmail()); ?></p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}