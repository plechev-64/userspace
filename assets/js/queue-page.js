document.addEventListener('DOMContentLoaded', () => {
    const pingButton = document.getElementById('usp-send-ping-btn');
    const processNowButton = document.getElementById('usp-process-now-btn');
    const gridContainer = document.querySelector('.usp-table-grid-container');

    if (!gridContainer) {
        return;
    }

    const UspCore = window.UspCore;
    const pageData = window.uspQueuePageData || {};

    const statusTextEl = document.getElementById('usp-queue-status-text');
    const statusIndicatorEl = statusTextEl ? statusTextEl.querySelector('.usp-queue-status-indicator') : null;
    const statusStrongEl = statusTextEl ? statusTextEl.querySelector('strong') : null;
    const logEl = document.getElementById('usp-queue-log');
    const gridItemsEl = gridContainer.querySelector('.usp-grid-items-list');
    const gridPaginationEl = gridContainer.querySelector('.usp-grid-pagination');

    const stateTextMap = {
        running: 'Running',
        idle: 'Idle',
        stalled: 'Stalled',
    };

    if (pingButton) {
        pingButton.addEventListener('click', async (e) => {
            e.preventDefault();
            await handleButtonClick(pingButton, pageData.pingEndpoint, pageData.ping_sending);
        });
    }

    if (processNowButton) {
        processNowButton.addEventListener('click', async (e) => {
            e.preventDefault();
            await handleButtonClick(processNowButton, pageData.processEndpoint, pageData.processing);
        });
    }

    /**
     * Универсальный обработчик для кнопок-действий.
     * @param {HTMLButtonElement} button - Кнопка, на которую нажали.
     * @param {string} endpoint - REST эндпоинт для запроса.
     * @param {string} loadingText - Текст на кнопке во время выполнения запроса.
     */
    const handleButtonClick = async (button, endpoint, loadingText) => {
        const originalText = button.textContent;
        button.textContent = loadingText || 'Processing...';
        button.disabled = true;

        try {
            const response = await UspCore.api.post(endpoint);
            UspCore.ui.showNotice(response.message, 'success');

            // Сразу после успешной отправки запускаем обновление данных
            setTimeout(fetchStatus, 1000);

        } catch (error) {
            const errorMessage = error.message || 'An unknown error occurred.';
            UspCore.ui.showNotice(errorMessage, 'error');
        } finally {
            button.textContent = originalText;
            button.disabled = false;
        }
    };

    /**
     * Запрашивает и обновляет данные о состоянии очереди и гриде.
     */
    const fetchStatus = async () => {
        try {
            // Собираем текущие параметры грида для запроса
            const gridState = gridContainer.dataset.gridState ? JSON.parse(gridContainer.dataset.gridState) : {
                page: 1,
                orderby: 'id',
                order: 'desc'
            };

            // Формируем URL в формате "ключ/значение"
            const path = `${pageData.statusEndpoint}/page/${gridState.page}/orderby/${gridState.orderby}/order/${gridState.order}`;
            const data = await UspCore.api.get(path);

            // Обновляем виджет статуса
            if (data.status_widget) {
                updateStatusWidget(data.status_widget);
            }

            // Обновляем грид
            if (data.grid && gridItemsEl && gridPaginationEl) {
                gridItemsEl.innerHTML = data.grid.items_html;
                gridPaginationEl.innerHTML = data.grid.pagination_html;
            }

        } catch (error) {
            console.error('Failed to fetch queue status:', error);
        }
    };

    /**
     * Обновляет DOM-элементы виджета статуса.
     * @param {object} statusData
     */
    const updateStatusWidget = (statusData) => {
        if (statusIndicatorEl) {
            statusIndicatorEl.className = `usp-queue-status-indicator ${statusData.state}`;
        }
        if (statusStrongEl) {
            statusStrongEl.textContent = stateTextMap[statusData.state] || 'Unknown';
        }
        if (logEl) {
            logEl.innerHTML = statusData.log.length > 0
                ? statusData.log.map(line => document.createTextNode(line + '\n'))
                    .reduce((acc, node) => (acc.appendChild(node), acc), document.createDocumentFragment())
                    .textContent
                : 'No recent activity.';
        }
    };

    /**
     * Инициализирует Server-Sent Events для получения обновлений в реальном времени.
     */
    const initQueueSse = async () => {
        let debounceTimer;
        const UspCore = window.UspCore;
        const pageData = window.uspQueuePageData || {};

        /**
         * Функция-обертка с "дебаунсом" для предотвращения лавины запросов.
         * Запускает fetchStatus только один раз после серии быстрых событий.
         */
        const debouncedFetchStatus = () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                fetchStatus();
            }, 500); // Пауза в 500 мс
        };

        let finalSseUrl;
        // Если пользователь авторизован, получаем токен и добавляем к URL
        if (UspCore.isUserLoggedIn()) {
            try {
                const auth = await UspCore.api.post('/user/sse-token');
                if (pageData.uspLightWeightWorker) {
                    const params = new URLSearchParams({token: auth.token, signature: auth.signature});
                    finalSseUrl = `${pageData.uspLightWeightWorker}?${params.toString()}`;
                } else {
                    finalSseUrl = `${baseSseUrl}/token/${auth.token}/signature/${auth.signature}`;
                }
            } catch (e) {
                console.error("Could not get SSE token, connecting as anonymous.", e);
            }
        }
        const sseClient = UspCore.createSseClient(finalSseUrl);

        sseClient.on('batch_processed', (data) => {
            if (data && typeof data.JobsProcessed !== 'undefined') {
                console.log(`Received batch_processed event. Jobs processed: ${data.JobsProcessed}. Debouncing status fetch.`);
                // Вызываем обертку вместо прямого вызова fetchStatus()
                debouncedFetchStatus();
            }
        });

        sseClient.connect();
    };

    // Запускаем SSE
    initQueueSse();
});