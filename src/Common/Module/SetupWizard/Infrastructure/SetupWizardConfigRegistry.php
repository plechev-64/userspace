<?php

namespace UserSpace\Common\Module\SetupWizard\Infrastructure;

use UserSpace\Common\Module\SetupWizard\Domain\SetupWizardConfig;
use UserSpace\Common\Module\SetupWizard\Domain\SetupWizardConfigRegistryInterface;

if (!defined('ABSPATH')) {
    exit;
}

class SetupWizardConfigRegistry implements SetupWizardConfigRegistryInterface
{
    /**
     * @var callable[]
     */
    private array $configurators = [];

    public function __construct(
        private readonly SetupWizardConfig $wizardConfig
    )
    {
    }

    public function register(callable $configurator): void
    {
        $this->configurators[] = $configurator;
    }

    public function getWizardConfig(): SetupWizardConfig
    {
        foreach ($this->configurators as $configurator) {
            call_user_func($configurator, $this->wizardConfig);
        }

        return $this->wizardConfig;
    }
}