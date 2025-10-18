<?php

namespace UserSpace\Controller\Admin;

use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;
use UserSpace\Form\FormFactory;

#[Route(path: '/tab-settings')]
class TabSettingsController extends AbstractController
{
    public function __construct(
        private readonly FormFactory $formFactory
    ) {
    }

    #[Route(path: '/get', method: 'POST', permission: 'manage_options')]
    public function getTabSettingsForm(Request $request): JsonResponse
    {
        $tabConfig = json_decode($request->getPost('tabConfig', '{}'), true);

        $settingsFields = $this->getSettingsFields();

        // Заполняем поля формы текущими значениями из конфигурации вкладки
        if (!empty($tabConfig)) {
            foreach ($settingsFields as $settingName => &$settingConfig) {
                if (isset($tabConfig[$settingName])) {
                    $settingConfig['value'] = $tabConfig[$settingName];
                }
            }
        }

        $settingsFormConfig = ['sections' => [['blocks' => [['fields' => $settingsFields]]]]];
        $settingsForm = $this->formFactory->create($settingsFormConfig);

        return $this->success(['html' => '<form class="usp-form">' . $settingsForm->render() . '</form>']);
    }

    /**
     * Определяет поля для формы настроек вкладки.
     * @return array
     */
    private function getSettingsFields(): array
    {
        return [
            'title' => [
                'type' => 'text',
                'label' => __('Title', 'usp'),
                'rules' => ['required' => true],
            ],
            'icon' => [
                'type' => 'text',
                'label' => __('Icon', 'usp'),
                'description' => sprintf(
                /* translators: %s: link to Dashicons documentation */
                    __('Enter a Dashicon class name. See all available icons %s.', 'usp'),
                    '<a href="https://developer.wordpress.org/resource/dashicons/" target="_blank">' . __('here', 'usp') . '</a>'
                ),
            ],
            'capability' => [
                'type' => 'text',
                'label' => __('Capability', 'usp'),
                'description' => __('Required capability to view this tab (e.g., "read", "edit_posts").', 'usp'),
                'value' => 'read', // Default value
            ],
            'isPrivate' => [
                'type' => 'boolean',
                'label' => __('Private Tab', 'usp'),
                'description' => __('If checked, the tab will only be visible to the profile owner.', 'usp'),
            ],
            // Другие настройки, такие как contentType, contentSource, можно добавить здесь в будущем.
        ];
    }
}