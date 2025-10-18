<?php

namespace UserSpace\Core\Grid\DTO;

class GridRequestParamsDto
{
    public readonly int $page;
    public readonly int $perPage;
    public readonly string $search;
    public readonly string $orderBy;
    public readonly string $order;

    /**
     * @param array<string, mixed> $params
     */
    public function __construct(array $params)
    {
        $this->page = max(1, (int)($params['page'] ?? 1));
        $this->perPage = (int)($params['per_page'] ?? 20);
        $this->search = sanitize_text_field($params['search'] ?? '');
        $this->orderBy = sanitize_text_field($params['orderby'] ?? 'id');

        $order = strtoupper($params['order'] ?? 'DESC');
        $this->order = in_array($order, ['ASC', 'DESC']) ? $order : 'DESC';
    }
}