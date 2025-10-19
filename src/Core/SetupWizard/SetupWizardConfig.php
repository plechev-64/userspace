<?php

namespace UserSpace\Core\SetupWizard;

use UserSpace\Module\Form\Src\Domain\Field\DTO\FieldDto;

/**
 * Конфигуратор для пошагового мастера настройки.
 * Позволяет гибко определять шаги и поля для каждого шага.
 */
class SetupWizardConfig
{
    private array $steps = [];
    private int $currentStepIndex = -1;

    /**
     * Добавляет новый шаг в мастер настройки.
     *
     * @param string $id Уникальный идентификатор шага.
     * @param string $title Заголовок шага.
     * @return self
     */
    public function addStep(string $id, string $title): self
    {
        $this->currentStepIndex++;
        $this->steps[$this->currentStepIndex] = [
            'id' => $id,
            'title' => $title,
            'fields' => [],
        ];
        return $this;
    }

    /**
     * Добавляет поле (опцию) в текущий шаг.
     *
     * @param FieldDto $fieldDto
     * @return self
     */
    public function addOption(FieldDto $fieldDto): self
    {
        if ($this->currentStepIndex >= 0) {
            $this->steps[$this->currentStepIndex]['fields'][$fieldDto->name] = $fieldDto->toArray();
        }
        return $this;
    }

    public function toArray(): array
    {
        return ['steps' => $this->steps];
    }
}