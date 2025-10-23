<?php

namespace UserSpace\Common\Module\SetupWizard\App\UseCase\SaveStep;

/**
 * Команда для сохранения данных шага мастера установки.
 */
class SaveWizardStepCommand
{
    /**
     * @param array<string, string|array> $stepData
     */
    public function __construct(
        public readonly array $stepData
    )
    {
    }
}