<?php

namespace UserSpace\Admin\SetupWizard;

use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Option\OptionManagerInterface;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;
use UserSpace\Core\String\StringFilterInterface;

#[Route(path: '/setup-wizard')]
class SetupWizardController extends AbstractController
{
    private const OPTION_NAME = 'usp_settings';

    public function __construct(
        private readonly StringFilterInterface  $str,
        private readonly OptionManagerInterface $optionManager
    )
    {
    }

    /**
     * Сохраняет данные одного шага мастера.
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[Route(path: '/save-step', method: 'POST', permission: 'manage_options')]
    public function saveStep(Request $request): JsonResponse
    {
        $data = $request->getPost('data', []);

        if (empty($data) || !is_array($data)) {
            return $this->error($this->str->translate('No data to save.'), 400);
        }

        $options = $this->optionManager->get(self::OPTION_NAME, []);
        $sanitized_data = [];

        foreach ($data as $key => $value) {
            $sanitized_data[$this->str->sanitizeKey($key)] = $this->str->sanitizeTextField($value);
        }

        $new_options = array_merge($options, $sanitized_data);
        $this->optionManager->update(self::OPTION_NAME, $new_options);

        return $this->success([
            'message' => $this->str->translate('Step settings saved successfully.')
        ]);
    }
}