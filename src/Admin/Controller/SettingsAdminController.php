<?php

namespace UserSpace\Admin\Controller;

use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;

#[Route(path: '/admin')]
class SettingsAdminController extends AbstractController
{
    private const OPTION_NAME = 'usp_settings';

    #[Route(path: '/settings', method: 'POST', permission: 'manage_options')]
    public function saveSettings(Request $request): JsonResponse
    {
        $settings = [];
        // Мы не можем использовать getPost() напрямую, так как данные приходят как JSON payload
        $payload = json_decode(file_get_contents('php://input'), true);

        if (is_array($payload)) {
            foreach ($payload as $key => $value) {
                if (is_array($value)) {
                    $settings[sanitize_key($key)] = array_map('sanitize_text_field', $value);
                } else {
                    $settings[sanitize_key($key)] = sanitize_text_field($value);
                }
            }
        }

        update_option(self::OPTION_NAME, $settings);

        return $this->success(['message' => __('Settings saved successfully.', 'usp')]);
    }
}