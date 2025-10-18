<?php

namespace UserSpace\Core\Tabs;

class TabDto
{
    public string $id;
    public string $title;
    public string $location = 'main'; // e.g., 'main', 'sidebar', 'header'
    public int $order = 100;
    public ?string $parentId = null;
    public bool $isPrivate = false;
    public string $capability = 'read';
    public ?string $icon = null;
    public string $contentType = 'rest'; // 'callback', 'rest', 'modal'
    public mixed $contentSource = null; // mixed: callback function, rest_url, etc.

    /** @var TabDto[] */
    public array $subTabs = [];

    public function __construct(string $id, string $title, ?string $parentId = null)
    {
        $this->id = $id;
        $this->title = $title;
        $this->parentId = $parentId;
    }
}