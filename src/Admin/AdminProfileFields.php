<?php

namespace UserSpace\Admin;

use UserSpace\Common\Module\Form\Src\Infrastructure\Factory\FormFactory;
use UserSpace\Common\Module\Form\Src\Infrastructure\Form\FormManager;
use UserSpace\Common\Module\User\Src\Domain\UserApiInterface;
use UserSpace\Core\Hooks\HookManagerInterface;
use UserSpace\Core\Http\Request;
use UserSpace\Core\String\StringFilterInterface;

/**
 * Управляет отображением и сохранением кастомных полей на странице профиля пользователя в админ-панели.
 */
class AdminProfileFields
{
    public function __construct(
        private readonly FormManager           $formManager,
        private readonly FormFactory           $formFactory,
        private readonly StringFilterInterface $str,
        private readonly HookManagerInterface  $hookManager,
        private readonly UserApiInterface      $userApi,
        private readonly Request               $request
    )
    {
    }

    /**
     * Регистрирует хуки WordPress для отображения и сохранения полей.
     */
    public function registerHooks(): void
    {
        $this->hookManager->addAction('show_user_profile', [$this, 'renderProfileFields']);
        $this->hookManager->addAction('edit_user_profile', [$this, 'renderProfileFields']);

        $this->hookManager->addAction('personal_options_update', [$this, 'saveProfileFields']);
        $this->hookManager->addAction('edit_user_profile_update', [$this, 'saveProfileFields']);
    }

    /**
     * @param \WP_User $user
     * @todo избавится от WP_User
     * Рендерит кастомные поля на странице профиля.
     */
    public function renderProfileFields(\WP_User $user): void
    {
        $config = $this->formManager->load('profile');
        if (!$config) {
            return;
        }

        // Получаем все поля из конфигурации
        $fields = $config->getFields();

        foreach (array_keys($fields) as $fieldName) {
            // Пропускаем поля, которые WordPress рендерит и обрабатывает сам
            if (in_array($fieldName, ['user_login', 'user_email', 'display_name', 'user_pass', 'password_repeat'])) {
                $config->removeField($fieldName);
                continue;
            }
            $config->updateFieldValue($fieldName, $user->$fieldName ?? $this->userApi->getUserMeta($user->ID, $fieldName, true));
        }

        $form = $this->formFactory->create($config);

        echo '<h2>' . $this->str->translate('Additional Information') . '</h2>';
        echo '<table class="form-table" role="presentation">';
        // Рендерим форму в специальном режиме для админ-панели
        echo $form->render(true);
        echo '</table>';
    }

    /**
     * Сохраняет значения кастомных полей.
     * @param int $userId
     */
    public function saveProfileFields(int $userId): void
    {
        if (!$this->userApi->currentUserCan('edit_user', $userId)) {
            return;
        }

        $config = $this->formManager->load('profile');
        if (!$config) {
            return;
        }

        // Получаем все поля из конфигурации, чтобы знать, какие данные ожидать
        $fields = $config->getFields();

        foreach (array_keys($fields) as $fieldName) {
            // Сохраняем только если поле было отправлено в POST
            if ($this->request->getPost($fieldName) !== null) {
                // Пропускаем основные поля, WordPress сохраняет их сам
                if (in_array($fieldName, ['user_login', 'user_email', 'display_name', 'user_pass', 'password_repeat'])) {
                    continue;
                }

                // Здесь можно добавить более сложную санацию в зависимости от типа поля
                $value = $this->str->unslash($this->request->getPost($fieldName));
                $this->userApi->updateUserMeta($userId, $fieldName, $value);
            }
        }
    }
}