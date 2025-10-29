<?php

namespace UserSpace\Common\Module\SetupWizard\App\Controller;

use UserSpace\Common\Module\Form\Src\Domain\Form\Config\FormConfig;
use UserSpace\Common\Module\Form\Src\Domain\Service\FormSanitizerInterface;
use UserSpace\Common\Module\SetupWizard\App\UseCase\SaveStep\SaveWizardStepCommand;
use UserSpace\Common\Module\SetupWizard\App\UseCase\SaveStep\SaveWizardStepUseCase;
use UserSpace\Common\Module\SetupWizard\Domain\SetupWizardConfigRegistryInterface;
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
        private readonly StringFilterInterface              $str,
        private readonly SaveWizardStepUseCase              $saveWizardStepUseCase,
        private readonly SetupWizardConfigRegistryInterface $wizardConfigRegistry,
        private readonly FormSanitizerInterface             $formSanitizer
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
        $stepId = $request->getPost('stepId');
        $rawData = $request->getPost('data', []);

        if (empty($stepId) || !is_string($stepId)) {
            return $this->error(['message' => $this->str->translate('Step ID is missing or invalid.')], 400);
        }

        // 1. Получаем полную конфигурацию мастера
        $wizardConfig = $this->wizardConfigRegistry->getWizardConfig();
        $steps = $wizardConfig->toArray()['steps'];

        // 2. Находим конфигурацию для нужного шага
        $currentStepConfig = null;
        foreach ($steps as $step) {
            if ($step['id'] === $stepId) {
                $currentStepConfig = $step;
                break;
            }
        }

        if (!$currentStepConfig) {
            return $this->error(['message' => $this->str->translate('Step configuration not found.')], 404);
        }

        // 3. Создаем временный FormConfig и санируем данные
        $stepFormConfig = new FormConfig();
        $stepFormConfig->addSection('')->addBlock('');
        foreach ($currentStepConfig['fields'] as $name => $fieldData) {
            $stepFormConfig->addField($name, $fieldData);
        }

        $clearedData = $this->formSanitizer->sanitize($stepFormConfig, $rawData);

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