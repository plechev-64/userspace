<?php

namespace UserSpace\Core\SetupWizard;

use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;

#[Route(path: '/setup-wizard')]
class SetupWizardController extends AbstractController
{
    private const OPTION_NAME = 'usp_settings';

    /**
     * Сохраняет данные одного шага мастера.
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[Route(path: '/save-step', method: 'POST', permission: 'manage_options')]
    public function saveStep(Request $request): JsonResponse
    {
        // Мы не можем использовать getPost() напрямую, так как данные приходят как JSON payload
        $payload = json_decode(file_get_contents('php://input'), true);

        $data = $payload['data'] ?? [];

        if (empty($data) || !is_array($data)) {
            return $this->error(__('No data to save.', 'usp'), 400);
        }

        $options = get_option(self::OPTION_NAME, []);
        $sanitized_data = [];

        foreach ($data as $key => $value) {
            $sanitized_data[sanitize_key($key)] = sanitize_text_field($value);
        }

        $new_options = array_merge($options, $sanitized_data);
        update_option(self::OPTION_NAME, $new_options);

        return $this->success([
            'message' => __('Step settings saved successfully.', 'usp')
        ]);
    }
}