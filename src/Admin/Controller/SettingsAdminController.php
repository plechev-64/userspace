<?php

namespace UserSpace\Admin\Controller;

use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Option\OptionManagerInterface;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;
use UserSpace\Core\String\StringFilterInterface;

#[Route(path: '/admin')]
class SettingsAdminController extends AbstractController
{
    private const OPTION_NAME = 'usp_settings';

    public function __construct(
        private readonly StringFilterInterface  $str,
        private readonly OptionManagerInterface $optionManager
    )
    {
    }

    #[Route(path: '/settings', method: 'POST', permission: 'manage_options')]
    public function saveSettings(Request $request): JsonResponse
    {
        $settings = [];

        $payload = $request->getPostParams();

        foreach ($payload as $key => $value) {
            $settings[$this->str->sanitizeKey($key)] = $this->str->sanitizeTextField($value);
        }

        $this->optionManager->update(self::OPTION_NAME, $settings);

        return $this->success(['message' => $this->str->translate('Settings saved successfully.')]);
    }
}