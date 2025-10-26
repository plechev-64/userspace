<?php

namespace UserSpace\Common\Module\SetupWizard\App\Controller;

use UserSpace\Common\Module\SetupWizard\App\UseCase\SaveStep\SaveWizardStepCommand;
use UserSpace\Common\Module\SetupWizard\App\UseCase\SaveStep\SaveWizardStepUseCase;
use UserSpace\Core\Exception\UspException;
use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Attributes\Route;
use UserSpace\Core\Sanitizer\SanitizerInterface;
use UserSpace\Core\String\StringFilterInterface;

#[Route(path: '/setup-wizard')]
class SetupWizardController extends AbstractController
{
    public function __construct(
        private readonly StringFilterInterface $str,
        private readonly SaveWizardStepUseCase $saveWizardStepUseCase,
        private readonly SanitizerInterface    $sanitizer
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
        $rawData = $request->getPost('data', []);
        $clearedData = $this->sanitizer->sanitize($rawData, []); // Use default sanitization (TEXT_FIELD)

        $command = new SaveWizardStepCommand($clearedData->all());

        try {
            $this->saveWizardStepUseCase->execute($command);
            return $this->success([
                'message' => $this->str->translate('Step settings saved successfully.')
            ]);
        } catch (UspException $e) {
            return $this->error(['message' => $e->getMessage()], $e->getCode());
        }
    }
}