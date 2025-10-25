<?php

namespace UserSpace\Common\Module\Locations\Src\Domain;

use UserSpace\Common\Module\User\Src\Domain\UserApiInterface;

abstract class AbstractButton implements ItemInterface
{
    protected string $id;
    protected string $title;
    protected string $location = 'main';
    protected int $order = 100;
    protected ?string $parentId = null;
    protected ?string $icon = null;
    protected string $capability = 'read';
    protected bool $isPrivate = false;
    protected ?string $actionEndpoint = null;
    protected UserApiInterface $userApi;

    public function __construct(UserApiInterface $userApi)
    {
        $this->userApi = $userApi;
    }

    /**
     * Основная логика, выполняемая при нажатии на кнопку.
     * @param array $requestData Данные из запроса.
     * @return mixed Результат выполнения.
     */
    abstract public function handleAction(array $requestData): mixed;

    public function getItemType(): string
    {
        return 'button';
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function getParentId(): ?string
    {
        return $this->parentId;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getActionEndpoint(): ?string
    {
        if ($this->actionEndpoint) {
            return $this->actionEndpoint;
        }
        // По умолчанию формируем эндпоинт на основе ID
        return "/location/item/action/{$this->getId()}";
    }

    public function isPrivate(): bool
    {
        return $this->isPrivate;
    }

    public function canView(): bool
    {
        return true;
    }

    /**
     * Обновляет свойства объекта из массива данных.
     *
     * @param array $data Ассоциативный массив с данными для обновления.
     */
    public function updateFromArray(array $data): void
    {
        // ID можно установить только один раз, если он еще не инициализирован.
        if (empty($this->id) && !empty($data['id'])) {
            $this->id = (string)$data['id'];
        }

        if (isset($data['title'])) {
            $this->title = (string)$data['title'];
        }
        if (isset($data['location'])) {
            $this->location = (string)$data['location'];
        }
        if (isset($data['order'])) {
            $this->order = (int)$data['order'];
        }
        // parentId может быть null
        if (array_key_exists('parentId', $data)) {
            $this->parentId = $data['parentId'] ? (string)$data['parentId'] : null;
        }
        if (isset($data['isPrivate'])) {
            $this->isPrivate = (bool)$data['isPrivate'];
        }
        if (isset($data['capability'])) {
            $this->capability = (string)$data['capability'];
        }
        if (isset($data['icon'])) {
            $this->icon = $data['icon'] ? (string)$data['icon'] : null;
        }
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'itemType' => $this->getItemType(),
            'location' => $this->getLocation(),
            'order' => $this->getOrder(),
            'parentId' => $this->getParentId(),
            'isPrivate' => $this->isPrivate(),
            'capability' => $this->capability,
            'icon' => $this->getIcon(),
            'actionEndpoint' => $this->getActionEndpoint(),
        ];
    }
}