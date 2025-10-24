<?php

namespace UserSpace\Common\Module\Tabs\App\Tabs;

use UserSpace\Common\Module\Tabs\Src\Domain\AbstractButton;
use UserSpace\Common\Module\User\Src\Domain\UserApiInterface;

class ClearCacheButton extends AbstractButton
{
    protected string $id = 'clear_cache';
    protected string $title = 'Clear Cache';
    protected ?string $icon = 'dashicons-trash';
    protected string $capability = 'manage_options'; // Доступно только администраторам

    public function __construct(UserApiInterface $userApi)
    {
        parent::__construct($userApi);
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
            'message' => 'Cache cleared successfully!'
        ];
    }
}