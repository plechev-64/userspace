/**
 * Инициализирует один компонент табличного грида.
 * @param {HTMLElement} gridContainer - DOM-элемент контейнера грида (.usp-table-grid-container).
 */
function initTableGrid(gridContainer) {
    // Предотвращаем повторную инициализацию
    if (gridContainer.dataset.uspTableGridInitialized) {
        return;
    }
    gridContainer.dataset.uspTableGridInitialized = 'true';

    const UspCore = window.UspCore;
    if (!UspCore || !UspCore.api) {
        console.error('USP Core not found.');
        return;
    }

    // Убедимся, что объект локализации существует
    const uspGridL10n = window.uspGridL10n || { text: { error: 'An error occurred.' } };

    const gridId = gridContainer.id;
    if (!gridId) {
        return;
    }

    const searchInput = gridContainer.querySelector('.usp-grid-search-input');
    const searchButton = gridContainer.querySelector('.usp-grid-search-button');
    const itemsList = gridContainer.querySelector('.usp-grid-items-list');
    const paginationContainer = gridContainer.querySelector('.usp-grid-pagination');
    const loader = gridContainer.querySelector('.usp-grid-loader');
    const settingsToggle = gridContainer.querySelector('.usp-grid-settings-toggle');
    const settingsDropdown = gridContainer.querySelector('.usp-grid-settings-dropdown');
    const endpoint = gridContainer.dataset.endpoint;

    if (!endpoint) {
        console.error('Table Grid initialization error: data-endpoint attribute is missing.', gridContainer);
        return;
    }

    let currentPage = 1;
    let currentSearch = '';
    let currentOrderBy = 'id';
    let currentOrder = 'desc';
    let requestInProgress = false;

    const fetchData = async (page = 1, search = '', orderby = 'id', order = 'desc') => {
        if (requestInProgress) return;
        requestInProgress = true;
        if (loader) loader.style.display = 'flex';

        const body = new FormData();
        body.append('page', page);
        body.append('search', search);
        body.append('orderby', orderby);
        body.append('order', order);

        try {
            const data = await UspCore.api.post(endpoint, body);
            currentPage = data.current_page || 1;
            currentSearch = search;
            currentOrderBy = orderby;
            currentOrder = order;

            // Сохраняем текущее состояние грида в data-атрибут для внешнего доступа
            gridContainer.dataset.gridState = JSON.stringify({
                page: currentPage,
                orderby: currentOrderBy,
                order: currentOrder,
            });

            if (itemsList) itemsList.innerHTML = data.items_html || '';
            if (paginationContainer) paginationContainer.innerHTML = data.pagination_html || '';

            updateSortableHeaders();
            updateColumnVisibility();
        } catch (error) {
            console.error('Grid fetch error:', error);
            if (itemsList) itemsList.innerHTML = `<p>${error.message || uspGridL10n.text.error}</p>`;
            if (paginationContainer) paginationContainer.innerHTML = '';
        } finally {
            requestInProgress = false;
            if (loader) loader.style.display = 'none';
        }
    };

    // Initial data load
    fetchData(currentPage, currentSearch, currentOrderBy, currentOrder);

    // Search handler
    const performSearch = () => {
        const searchTerm = searchInput.value.trim();
        if (searchTerm !== currentSearch) {
            fetchData(1, searchTerm, currentOrderBy, currentOrder);
        }
    };

    if (searchButton && searchInput) {
        searchButton.addEventListener('click', performSearch);
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
    }

    // Pagination handler
    if (paginationContainer) {
        paginationContainer.addEventListener('click', (e) => {
            e.preventDefault();
            const target = e.target.closest('a.page-numbers');
            if (!target) return;

            const page = parseInt(target.textContent, 10) || 1;

            fetchData(page, currentSearch, currentOrderBy, currentOrder);
        });
    }

    // Sort handler
    gridContainer.addEventListener('click', (e) => {
        const th = e.target.closest('th.sortable');
        if (!th) return;
        e.preventDefault();

        const key = th.dataset.sortKey;
        let order = 'asc';

        if (key === currentOrderBy) {
            order = currentOrder === 'asc' ? 'desc' : 'asc';
        }

        fetchData(1, currentSearch, key, order);
    });

    const updateSortableHeaders = () => {
        if (!itemsList) {
            return;
        }
        const headers = itemsList.querySelectorAll('th.sortable');
        headers.forEach(th => {
            th.classList.remove('asc', 'desc');
            if (th.dataset.sortKey === currentOrderBy) {
                th.classList.add(currentOrder);
            }
        });
    };

    // Column visibility handler
    if (settingsToggle && settingsDropdown) {
        settingsToggle.addEventListener('click', () => {
            settingsDropdown.style.display = settingsDropdown.style.display === 'block' ? 'none' : 'block';
        });

        document.addEventListener('click', (e) => {
            if (!settingsToggle.contains(e.target) && !settingsDropdown.contains(e.target)) {
                settingsDropdown.style.display = 'none';
            }
        });

        settingsDropdown.addEventListener('change', (e) => {
            if (e.target.matches('input[type="checkbox"]')) {
                updateColumnVisibility();
            }
        });
    }

    const updateColumnVisibility = () => {
        if (!settingsDropdown) {
            return;
        }
        const checkboxes = settingsDropdown.querySelectorAll('input[data-column-key]');
        checkboxes.forEach(checkbox => {
            const key = checkbox.dataset.columnKey;
            const cells = gridContainer.querySelectorAll(`.column-${key}`);
            cells.forEach(cell => cell.style.display = checkbox.checked ? '' : 'none');
        });
    };
}

// 1. Инициализация для гридов, которые уже есть на странице при загрузке.
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.usp-table-grid-container').forEach(initTableGrid);
});

// 2. Инициализация для гридов, загруженных динамически через REST.
document.addEventListener('usp:tabContentLoaded', (event) => {
    event.detail.pane.querySelectorAll('.usp-table-grid-container').forEach(initTableGrid);
});