<?php

namespace UserSpace\Common\Module\Tabs\Src\Domain;

abstract class AbstractTab
{
    protected string $id = '';
    protected string $title;
    protected string $location = 'main';
    protected int $order = 100;
    protected ?string $parentId = null;
    protected bool $isPrivate = false;
    protected string $capability = 'read';
    protected ?string $icon = null;
    protected string $contentType = 'rest';
    protected mixed $contentSource = null;
    protected array $subTabs = [];

    abstract public function getContent(): string;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
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

    public function isPrivate(): bool
    {
        return $this->isPrivate;
    }

    public function getCapability(): string
    {
        return $this->capability;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function getContentSource(): mixed
    {
        return $this->contentSource;
    }

    public function getSubTabs(): array
    {
        return $this->subTabs;
    }

    public function addSubTab(AbstractTab $subTab): void
    {
        $this->subTabs[] = $subTab;
    }

    /**
     * @param AbstractTab[] $subTabs
     */
    public function setSubTabs(array $subTabs): void
    {
        $this->subTabs = $subTabs;
    }

    public function setParentId(?string $parentId): void
    {
        $this->parentId = $parentId;
    }

    /**
     * Обновляет свойства вкладки из массива данных.
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
            'id' => $this->id,
            'title' => $this->title,
            'location' => $this->location,
            'order' => $this->order,
            'parentId' => $this->parentId,
            'isPrivate' => $this->isPrivate,
            'capability' => $this->capability,
            'icon' => $this->icon,
            'contentType' => $this->contentType,
        ];
    }
}