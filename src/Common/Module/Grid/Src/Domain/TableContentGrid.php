<?php

namespace UserSpace\Common\Module\Grid\Src\Domain;

use UserSpace\Common\Module\Grid\Src\Domain\DTO\GridRequestParamsDto;
use UserSpace\Core\AssetRegistryInterface;
use UserSpace\Core\Database\DatabaseConnectionInterface;
use UserSpace\Core\StringFilterInterface;

abstract class TableContentGrid extends AbstractListContentGrid
{
    public function __construct(
        DatabaseConnectionInterface $db,
        StringFilterInterface $str,
        AssetRegistryInterface $assetRegistry
    )
    {
        parent::__construct($db, $str, $assetRegistry);
    }

    /**
     * Возвращает конфигурацию колонок для таблицы.
     *
     * @return array<string, array{title: string, sortable?: bool}>
     */
    abstract protected function getColumnsConfig(): array;

    /**
     * Этот метод не используется в табличном гриде.
     * @throws \Exception
     */
    final protected function getItemTemplatePath(): string
    {
        // Табличный грид имеет собственный механизм рендеринга и не использует внешние шаблоны для элементов.
        return '';
    }

    /**
     * @inheritDoc
     */
    public function fetchData(GridRequestParamsDto $paramsDto): array
    {
        // Валидация сортировки по доступным колонкам
        $columnsConfig = $this->getColumnsConfig();
        if (!isset($columnsConfig[$paramsDto->orderBy]) || empty($columnsConfig[$paramsDto->orderBy]['sortable'])) {
            // Если сортировка по указанному полю не разрешена, используем первую сортируемую колонку или ID
            $defaultOrderBy = 'id';
            foreach ($columnsConfig as $key => $config) {
                if (!empty($config['sortable'])) {
                    $defaultOrderBy = $key;
                    break;
                }
            }
            $paramsDto = new GridRequestParamsDto((array)$paramsDto + ['orderby' => $defaultOrderBy]);
        }

        return parent::fetchData($paramsDto);
    }

    /**
     * Рендерит полный HTML-код грида с таблицей.
     */
    public function render(): string
    {
        ob_start();
        ?>
        <div id="<?= $this->str->escAttr($this->getId()); ?>" class="usp-grid-container usp-table-grid-container"
             data-endpoint="<?= $this->str->escAttr($this->getEndpointPath()); ?>">
            <div class="usp-grid-header">
                <div class="usp-grid-settings">
                    <button type="button" class="button usp-grid-settings-toggle">
                        <span class="dashicons dashicons-admin-settings"></span>
                        <?= $this->str->translate('Settings'); ?>
                    </button>
                    <div class="usp-grid-settings-dropdown">
                        <p><?= $this->str->translate('Toggle columns:'); ?></p>
                        <?php foreach ($this->getColumnsConfig() as $key => $column): ?>
                            <label>
                                <input type="checkbox" data-column-key="<?= $this->str->escAttr($key); ?>" checked>
                                <?= $this->str->escHtml($column['title']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="usp-grid-search">
                    <input type="search" class="usp-grid-search-input"
                           placeholder="<?= $this->str->escAttr($this->str->translate('Search...')); ?>">
                    <button type="button"
                            class="button usp-grid-search-button"><?= $this->str->translate('Search'); ?></button>
                </div>
            </div>

            <div class="usp-grid-body">
                <div class="usp-grid-items-list">
                    <?php // Сюда будет загружаться таблица через AJAX
                    ?>
                </div>
                <div class="usp-grid-loader">
                    <?= $this->str->translate('Loading...'); ?>
                </div>
            </div>

            <div class="usp-grid-footer">
                <div class="usp-grid-pagination">
                    <?php // Сюда будет загружаться пагинация
                    ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Рендерит HTML для таблицы с элементами.
     * @param array<object> $items
     */
    public function renderItems(array $items): string
    {
        $columns = $this->getColumnsConfig();

        ob_start();
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
            <tr>
                <?php foreach ($columns as $key => $column): ?>
                    <th scope="col"
                        class="manage-column column-<?= $this->str->escAttr($key); ?> <?= !empty($column['sortable']) ? 'sortable' : '' ?>"
                        data-sort-key="<?= $this->str->escAttr($key); ?>">
                        <a href="#">
                            <span><?= $this->str->escHtml($column['title']); ?></span>
                            <span class="sorting-indicator"></span>
                        </a>
                    </th>
                <?php endforeach; ?>
            </tr>
            </thead>
            <tbody id="the-list">
            <?php if (empty($items)): ?>
                <tr>
                    <td colspan="<?= count($columns); ?>"><?= $this->str->translate('No items found.'); ?></td>
                </tr>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <?php foreach ($columns as $key => $column): ?>
                            <td class="column-<?= $this->str->escAttr($key); ?>">
                                <?= $this->str->escHtml($item->{$key} ?? ''); ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
            <tfoot>
            <tr>
                <?php foreach ($columns as $key => $column): ?>
                    <th scope="col" class="manage-column column-<?= $this->str->escAttr($key); ?>">
                        <?= $this->str->escHtml($column['title']); ?>
                    </th>
                <?php endforeach; ?>
            </tr>
            </tfoot>
        </table>
        <?php
        return ob_get_clean();
    }
}