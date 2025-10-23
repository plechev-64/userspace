<?php

namespace UserSpace\Common\Module\SetupWizard\App\UseCase\SaveStep;

use UserSpace\Common\Module\Settings\Src\Domain\OptionManagerInterface;
use UserSpace\Core\Exception\UspException;
use UserSpace\Core\String\StringFilterInterface;

class SaveWizardStepUseCase
{
    private const OPTION_NAME = 'usp_settings';

    public function __construct(
        private readonly StringFilterInterface  $str,
        private readonly OptionManagerInterface $optionManager
    )
    {
    }

    /**
     * @throws UspException
     */
    public function execute(SaveWizardStepCommand $command): void
    {
        /** @todo передавать через команду понятные параметры, возможно массив объектов */
        $data = $command->stepData;

        if (empty($data) || !is_array($data)) {
            throw new UspException($this->str->translate('No data to save.'), 400);
        }

        $options = $this->optionManager->get(self::OPTION_NAME, []);
        $sanitized_data = [];

        foreach ($data as $key => $value) {
            // Санируем ключ
            $sanitizedKey = $this->str->sanitizeKey($key);

            // Санируем значение, учитывая, что оно может быть массивом
            if (is_array($value)) {
                $sanitized_data[$sanitizedKey] = array_map([$this->str, 'sanitizeTextField'], $value);
            } else {
                $sanitized_data[$sanitizedKey] = $this->str->sanitizeTextField((string)$value);
            }
        }

        $new_options = array_merge($options, $sanitized_data);
        $this->optionManager->update(self::OPTION_NAME, $new_options);
    }
}