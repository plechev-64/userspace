<?php

namespace UserSpace\Admin;

use UserSpace\Common\Module\Form\Src\Domain\Field\DTO\FieldDto;

/**
 * Конструктор для создания конфигурации страницы настроек.
 */
class SettingsConfig
{
    private array $config = ['sections' => []];
    private ?string $currentSectionId = null;
    private ?string $currentBlockId = null;

    /**
     * Добавляет новую секцию.
     * @param string $id
     * @param string $title
     * @return $this
     */
    public function addSection(string $id, string $title): self
    {
        $this->config['sections'][$id] = [
            'id' => $id,
            'title' => $title,
            'blocks' => [],
        ];
        $this->currentSectionId = $id;
        $this->currentBlockId = null; // Сбрасываем текущий блок
        return $this;
    }

    /**
     * Добавляет новый блок в текущую секцию.
     * @param string $id
     * @param string $title
     * @return $this
     */
    public function addBlock(string $id, string $title): self
    {
        if ($this->currentSectionId === null) {
            // Можно бросить исключение или создать секцию по умолчанию
            return $this;
        }
        $this->config['sections'][$this->currentSectionId]['blocks'][$id] = [
            'id' => $id,
            'title' => $title,
            'fields' => [],
        ];
        $this->currentBlockId = $id;
        return $this;
    }

    /**
     * Добавляет новую опцию (поле) в текущий блок.
     * @param FieldDto $dto
     * @return $this
     */
    public function addOption(FieldDto $dto): self
    {
        if ($this->currentSectionId === null || $this->currentBlockId === null) {
            return $this;
        }

        $this->config['sections'][$this->currentSectionId]['blocks'][$this->currentBlockId]['fields'][$dto->name] = $dto->toArray();
        return $this;
    }

    /**
     * Возвращает собранную конфигурацию в виде массива.
     * @return array
     */
    public function toArray(): array
    {
        // Преобразуем ассоциативные массивы в индексные для FormFactory
        $finalConfig = $this->config;
        $finalConfig['sections'] = array_values($finalConfig['sections']);
        foreach ($finalConfig['sections'] as &$section) {
            $section['blocks'] = array_values($section['blocks']);
        }
        return $finalConfig;
    }
}