<?php

namespace UserSpace\Admin;

use UserSpace\Form\FormFactory;
use UserSpace\Form\FormManager;

/**
 * Управляет отображением и сохранением кастомных полей на странице профиля пользователя в админ-панели.
 */
class AdminProfileFields
{
    public function __construct(
        private readonly FormManager $formManager,
        private readonly FormFactory $formFactory
    ) {
    }

    /**
     * Регистрирует хуки WordPress для отображения и сохранения полей.
     */
    public function registerHooks(): void
    {
        add_action('show_user_profile', [$this, 'renderProfileFields']);
        add_action('edit_user_profile', [$this, 'renderProfileFields']);

        add_action('personal_options_update', [$this, 'saveProfileFields']);
        add_action('edit_user_profile_update', [$this, 'saveProfileFields']);
    }

    /**
     * Рендерит кастомные поля на странице профиля.
     * @param \WP_User $user
     */
    public function renderProfileFields(\WP_User $user): void
    {
        $config = $this->formManager->load('profile');
        if ( ! $config) {
            return;
        }

		// Получаем все поля из конфигурации
		$fields = $config->getFields();

		foreach ( array_keys( $fields ) as $fieldName ) {
			// Пропускаем поля, которые WordPress рендерит и обрабатывает сам
			if ( in_array( $fieldName, [ 'user_login', 'user_email', 'display_name', 'user_pass', 'password_repeat' ] ) ) {
				$config->removeField( $fieldName );
				continue;
			}
			$config->updateFieldValue( $fieldName, $user->$fieldName ?? get_user_meta( $user->ID, $fieldName, true ) );
		}

        $form = $this->formFactory->create( $config );

        echo '<h2>' . __('Additional Information', 'usp') . '</h2>';
        echo '<table class="form-table" role="presentation">';
        // Рендерим форму в специальном режиме для админ-панели
        echo $form->render( true );
        echo '</table>';
    }

    /**
     * Сохраняет значения кастомных полей.
     * @param int $userId
     */
    public function saveProfileFields(int $userId): void
    {
        if ( ! current_user_can('edit_user', $userId)) {
            return;
        }

        $config = $this->formManager->load('profile');
        if ( ! $config) {
            return;
        }

		// Получаем все поля из конфигурации, чтобы знать, какие данные ожидать
		$fields = $config->getFields();

		foreach ( array_keys( $fields ) as $fieldName ) {
			// Сохраняем только если поле было отправлено в POST
			if ( isset( $_POST[ $fieldName ] ) ) {
				// Пропускаем основные поля, WordPress сохраняет их сам
				if ( in_array( $fieldName, [ 'user_login', 'user_email', 'display_name', 'user_pass', 'password_repeat' ] ) ) {
					continue;
				}

				// Здесь можно добавить более сложную санацию в зависимости от типа поля
				$value = wp_unslash( $_POST[ $fieldName ] );
				update_user_meta( $userId, $fieldName, $value );
			}
		}
    }
}