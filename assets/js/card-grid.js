/**
 * Инициализирует один компонент грида в виде карточек.
 * @param {HTMLElement} gridContainer - DOM-элемент контейнера грида.
 */
function initCardGrid(gridContainer) {
    // Предотвращаем повторную инициализацию
    if (gridContainer.dataset.uspCardGridInitialized) {
        return;
    }
    gridContainer.dataset.uspCardGridInitialized = 'true';

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
    const endpoint = gridContainer.dataset.endpoint;

    if (!endpoint) {
        console.error('Card Grid initialization error: data-endpoint attribute is missing.', gridContainer);
        return;
    }

    let currentPage = 1;
    let currentSearch = '';
    let requestInProgress = false;

    const fetchData = async (page = 1, search = '') => {
        if (requestInProgress) return;
        requestInProgress = true;
        if (loader) loader.style.display = 'flex';

        const body = new FormData();
        body.append('page', page);
        body.append('search', search);

        try {
            const data = await UspCore.api.post(endpoint, body);
            currentPage = data.current_page || 1;
            currentSearch = search;

            if (itemsList) itemsList.innerHTML = data.items_html || '';
            if (paginationContainer) paginationContainer.innerHTML = data.pagination_html || '';
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
    fetchData(currentPage, currentSearch);

    // Search handler
    const performSearch = () => {
        const searchTerm = searchInput.value.trim();
        if (searchTerm !== currentSearch) {
            fetchData(1, searchTerm);
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

            fetchData(page, currentSearch);
        });
    }
}

// 1. Инициализация для гридов, которые уже есть на странице при загрузке.
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.usp-grid-container:not(.usp-table-grid-container)').forEach(initCardGrid);
});

// 2. Инициализация для гридов, загруженных динамически через REST.
document.addEventListener('usp:tabContentLoaded', (event) => {
    event.detail.pane.querySelectorAll('.usp-grid-container:not(.usp-table-grid-container)').forEach(initCardGrid);
});