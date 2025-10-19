<?php

namespace UserSpace\Common\Module\Grid\Src\Domain;

use UserSpace\Common\Module\Grid\Src\Domain\DTO\GridRequestParamsDto;
use UserSpace\Core\Database\QueryBuilder;

abstract class AbstractListContentGrid
{
    protected string $gridId;

    public function __construct(protected readonly QueryBuilder $queryBuilder)
    {
        $this->gridId = uniqid('usp-grid-');
    }

    /**
     * Возвращает уникальный идентификатор грида.
     */
    public function getId(): string
    {
        return $this->gridId;
    }

    /**
     * Возвращает имя основной таблицы.
     */
    abstract protected function getTableName(): string;

    /**
     * Возвращает псевдоним основной таблицы.
     */
    abstract protected function getTableAlias(): string;

    /**
     * Возвращает список колонок для выборки.
     * @return array<string, string>
     */
    abstract protected function getSelectColumns(): array;

    /**
     * Возвращает конфигурацию для JOIN'ов.
     * @return array<int, array{type: string, table: string, alias: string, on: string}>
     */
    abstract protected function getJoins(): array;

    /**
     * Возвращает колонки, по которым возможен поиск.
     * @return string[]
     */
    abstract protected function getSearchableColumns(): array;

    /**
     * Возвращает путь к шаблону для одного элемента.
     */
    abstract protected function getItemTemplatePath(): string;

    /**
     * Возвращает путь к REST API эндпоинту для грида.
     */
    abstract public function getEndpointPath(): string;

    /**
     * Получает данные для грида на основе параметров запроса.
     * @param GridRequestParamsDto $paramsDto
     * @return array<string, mixed>
     */
    public function fetchData(GridRequestParamsDto $paramsDto): array
    {
        $this->queryBuilder
            ->select($this->getSelectColumns())
            ->from($this->getTableName(), $this->getTableAlias());

        foreach ($this->getJoins() as $join) {
            $this->queryBuilder->addJoin($join['type'], $join['table'], $join['alias'], $join['on']);
        }

        if (!empty($paramsDto->search) && !empty($this->getSearchableColumns())) {
            $this->queryBuilder->where(function (QueryBuilder $query) use ($paramsDto) {
                foreach ($this->getSearchableColumns() as $column) {
                    $query->orWhere($column, 'LIKE', '%' . $this->queryBuilder->getWpdb()->esc_like($paramsDto->search) . '%');
                }
            });
        }

        // Клонируем билдер для подсчета общего количества записей без лимитов
        $countQueryBuilder = clone $this->queryBuilder;
        $totalItems = $countQueryBuilder->count($this->getTableAlias() . '.ID');

        $this->queryBuilder
            ->orderBy($paramsDto->orderBy, $paramsDto->order)
            ->limit($paramsDto->perPage)
            ->offset(($paramsDto->page - 1) * $paramsDto->perPage);

        $items = $this->queryBuilder->get();

        return [
            'items' => $items,
            'total_items' => (int)$totalItems,
            'total_pages' => (int)ceil($totalItems / $paramsDto->perPage),
            'current_page' => $paramsDto->page,
        ];
    }

    /**
     * Рендерит полный HTML-код грида.
     */
    public function render(): string
    {
        ob_start();
        ?>
        <div id="<?= esc_attr($this->getId()); ?>" class="usp-grid-container" data-endpoint="<?= esc_attr($this->getEndpointPath()); ?>">
            <div class="usp-grid-header">
                <div class="usp-grid-search">
                    <input type="search" class="usp-grid-search-input" placeholder="<?= esc_attr__('Search...', 'usp'); ?>">
                    <button type="button" class="button usp-grid-search-button"><?= esc_html__('Search', 'usp'); ?></button>
                </div>
            </div>

            <div class="usp-grid-body">
                <div class="usp-grid-items-list">
                    <?php // Сюда будут загружаться элементы через AJAX ?>
                </div>
                <div class="usp-grid-loader">
                    <?= esc_html__('Loading...', 'usp'); ?>
                </div>
            </div>

            <div class="usp-grid-footer">
                <div class="usp-grid-pagination">
                    <?php // Сюда будет загружаться пагинация ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Рендерит HTML для списка элементов.
     * @param array<object> $items
     */
    public function renderItems(array $items): string
    {
        ob_start();
        if (empty($items)) {
            echo '<p>' . esc_html__('No items found.', 'usp') . '</p>';
        } else {
            foreach ($items as $item) {
                include $this->getItemTemplatePath();
            }
        }
        return ob_get_clean();
    }

    /**
     * Рендерит HTML для пагинации.
     * @param int $currentPage
     * @param int $totalPages
     */
    public function renderPagination(int $currentPage, int $totalPages): string
    {
        if ($totalPages <= 1) {
            return '';
        }

        ob_start();
        echo paginate_links([
            'base' => '#%#%',
            'format' => '',
            'total' => $totalPages,
            'current' => $currentPage,
            'prev_text' => __('&laquo; Prev', 'usp'),
            'next_text' => __('Next &raquo;', 'usp'),
            'add_fragment' => '',
            'type' => 'list',
        ]);
        return ob_get_clean();
    }
}