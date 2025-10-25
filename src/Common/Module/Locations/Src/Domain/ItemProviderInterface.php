<?php

namespace UserSpace\Common\Module\Locations\Src\Domain;

/**
 * Интерфейс для сервиса, который регистрирует элементы меню по умолчанию.
 */
interface ItemProviderInterface
{
    /**
     * Регистрирует все элементы по умолчанию (из кода) и применяет к ним сохраненную конфигурацию.
     * Если обнаруживаются новые элементы, обновляет сохраненную конфигурацию.
     */
    public function mergeRegisteredItemsAndConfig(): void;
}