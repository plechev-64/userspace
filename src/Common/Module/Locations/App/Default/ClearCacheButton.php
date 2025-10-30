<?php

namespace UserSpace\Common\Module\Locations\App\Default;

use UserSpace\Common\Module\Locations\Src\Domain\AbstractButton;
use UserSpace\Common\Module\User\Src\Domain\UserApiInterface;
use UserSpace\Core\String\StringFilterInterface;
use UserSpace\Core\TemplateManagerInterface;

class ClearCacheButton extends AbstractButton
{
    protected string $id = 'clear_cache';
    protected string $title = 'Clear Cache';
    protected ?string $icon = 'dashicons-trash';
    protected string $capability = 'manage_options'; // Доступно только администраторам

    public function __construct(
        UserApiInterface $userApi,
        private readonly StringFilterInterface $str,
        TemplateManagerInterface $templateManager
    )
    {
        parent::__construct($userApi, $templateManager);
    }

    /**
     * Логика, выполняемая при нажатии на кнопку.
     *
     * @param array $requestData Данные из запроса.
     * @return array Результат выполнения.
     */
    public function handleAction(array $requestData): array
    {
        // В реальном сценарии здесь можно было бы вызвать сервис для очистки кэша.
        // $this->cacheService->clearAll();

        // Для примера просто возвращаем сообщение об успехе.
        return [
            'message' => $this->str->translate('Cache cleared successfully!')
        ];
    }
}