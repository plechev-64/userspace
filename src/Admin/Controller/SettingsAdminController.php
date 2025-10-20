<?php

namespace UserSpace\Admin\Controller;

use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;
use UserSpace\Core\String\StringFilterInterface;

#[Route(path: '/admin')]
class SettingsAdminController extends AbstractController
{
    private const OPTION_NAME = 'usp_settings';

    public function __construct(private readonly StringFilterInterface $str)
    {
    }

    #[Route(path: '/settings', method: 'POST', permission: 'manage_options')]
    public function saveSettings(Request $request): JsonResponse
    {
        $settings = [];
        // Мы не можем использовать getPost() напрямую, так как данные приходят как JSON payload
        $payload = json_decode(file_get_contents('php://input'), true);

        if (is_array($payload)) {
            foreach ($payload as $key => $value) {
                $settings[$this->str->sanitizeKey($key)] = $this->str->sanitizeTextField($value);
            }
        }

        update_option(self::OPTION_NAME, $settings);

        return $this->success(['message' => $this->str->translate('Settings saved successfully.')]);
    }
}