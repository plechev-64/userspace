<?php

namespace UserSpace\Common\Module\Media\App\UseCase\Upload;

/**
 * DTO для хранения и стандартизации конфигурации загрузчика файлов.
 * Гарантирует одинаковый порядок ключей для генерации и валидации подписи.
 */
class UploaderConfig
{
    public function __construct(
        public readonly string  $name,
        public readonly bool    $multiple = false,
        public readonly ?string $allowedTypes = null,
        public readonly ?float  $maxSize = null,
        public readonly ?int    $minWidth = null,
        public readonly ?int    $minHeight = null,
        public readonly ?int    $maxWidth = null,
        public readonly ?int    $maxHeight = null
    )
    {
    }

    /**
     * Создает объект конфигурации из объекта команды.
     */
    public static function fromCommand(UploadFileCommand $command): self
    {
        return new self(
            $command->name,
            $command->multiple,
            $command->allowedTypes,
            $command->maxSize,
            $command->minWidth,
            $command->minHeight,
            $command->maxWidth,
            $command->maxHeight
        );
    }

    /**
     * Возвращает стандартизированный массив для генерации/проверки подписи.
     * Порядок ключей здесь критически важен.
     */
    public function toArray(): array
    {
        // Фильтруем null значения, чтобы они не попадали в подпись
        return array_filter([
            'name' => $this->name,
            'multiple' => $this->multiple,
            'allowedTypes' => $this->allowedTypes,
            'maxSize' => $this->maxSize,
            'minWidth' => $this->minWidth,
            'minHeight' => $this->minHeight,
            'maxWidth' => $this->maxWidth,
            'maxHeight' => $this->maxHeight,
        ], fn($value) => $value !== null);
    }
}