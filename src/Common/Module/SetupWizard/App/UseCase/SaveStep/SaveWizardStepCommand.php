<?php

namespace UserSpace\Common\Module\SetupWizard\App\UseCase\SaveStep;

/**
 * Команда для сохранения данных шага мастера установки.
 */
class SaveWizardStepCommand
{
    public function __construct(
        public readonly array $stepData
    ) {
    }
}