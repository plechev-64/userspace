document.addEventListener('DOMContentLoaded', function () {
    const builder = document.querySelector('[data-usp-tab-builder]');
    if (!builder || !window.UspCore) {
        return;
    }

    const l10n = { // TODO: Localize this via wp_localize_script
        saving: 'Saving...',
        unknownError: 'An unknown error occurred.',
        errorPrefix: 'Error: ',
        tabSettingsTitle: 'Tab Settings:',
        tabNameLabel: 'ID (read-only)',
        cancel: 'Cancel',
        save: 'Save',
        close: 'Close',
        loading: 'Loading...',
        settingsLoadError: 'Error loading settings',
    };

    // --- Инициализация SortableJS ---
    const initSortable = () => {
        const tabContainers = builder.querySelectorAll('[data-sortable="tabs"], [data-sortable="subtabs"]');
        tabContainers.forEach(container => {
            new Sortable(container, {
                animation: 150,
                handle: '.usp-tab-builder-tab-header',
                group: 'tabs', // Все вкладки в одной группе для перемещения между локациями
                onEnd: (evt) => {
                    // Можно добавить логику для обновления parentId при перемещении
                }
            });
        });
    };

    // --- Логика редактирования (пока заглушка) ---
    builder.addEventListener('click', async function (e) {
        const target = e.target.closest('[data-action="edit-tab"]');
        if (!target) {
            return;
        }
        e.preventDefault();
        const tabEl = target.closest('.usp-tab-builder-tab');
        await openEditModal(tabEl);
    });

    const openEditModal = async (tabEl) => {
        const currentConfig = tabEl.dataset.config;

        const formData = new FormData();
        formData.append('tabConfig', currentConfig);

        try {
            const json = await window.UspCore.api.post('/tab-settings/get', formData);
            buildAndShowEditModal(tabEl, json.html);
        } catch (error) {
            alert((l10n.errorPrefix || 'Error: ') + error.message);
        }
    };

    const buildAndShowEditModal = (tabEl, settingsHtml) => {
        const config = JSON.parse(tabEl.dataset.config || '{}');

        const modalHtml = `
            <div class="usp-modal-backdrop is-visible">
                <div class="usp-modal-content">
                    <div class="usp-modal-header">
                        <h2>${(l10n.tabSettingsTitle || 'Tab Settings:')} ${config.title}</h2>
                        <button type="button" class="usp-modal-close" aria-label="${l10n.close || 'Close'}">&times;</button>
                    </div>
                    <div class="usp-modal-body">
                        <div class="usp-form-group">
                            <label for="tab-setting-id">${l10n.tabNameLabel || 'ID (read-only)'}</label>
                            <input type="text" id="tab-setting-id" value="${config.id}" readonly disabled>
                        </div>
                        ${settingsHtml}
                    </div>
                    <div class="usp-modal-footer">
                        <button type="button" class="button" data-action="cancel-edit">${l10n.cancel || 'Cancel'}</button>
                        <button type="button" class="button button-primary" data-action="save-edit">${l10n.save || 'Save'}</button>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        const modal = document.querySelector('.usp-modal-backdrop');

        modal.addEventListener('click', (e) => {
            if (e.target.matches('.usp-modal-close') || e.target.matches('[data-action="cancel-edit"]')) {
                modal.remove();
            }

            if (e.target.matches('[data-action="save-edit"]')) {
                const newSettings = collectSettingsFromModal(modal);
                const oldConfig = JSON.parse(tabEl.dataset.config || '{}');

                // Обновляем только те свойства, которые есть в форме
                const newConfig = { ...oldConfig, ...newSettings };

                tabEl.dataset.config = JSON.stringify(newConfig);

                // Обновляем видимые элементы в конструкторе
                const titleEl = tabEl.querySelector('.tab-title');
                if (titleEl) {
                    titleEl.textContent = newConfig.title;
                }
                const iconEl = tabEl.querySelector('.dashicons');
                if (iconEl) {
                    iconEl.className = `dashicons ${newConfig.icon || 'dashicons-admin-page'}`;
                }

                modal.remove();
            }
        });
    };

    const collectSettingsFromModal = (modal) => {
        const newSettings = {};
        const inputs = modal.querySelectorAll('input[type="text"], input[type="checkbox"]:checked, select');
        const allCheckboxes = modal.querySelectorAll('input[type="checkbox"]');

        // Устанавливаем false для всех чекбоксов
        allCheckboxes.forEach(checkbox => {
            if (checkbox.name) {
                newSettings[checkbox.name] = false;
            }
        });

        // Перезаписываем на true, если они отмечены
        inputs.forEach(input => {
            if (input.name) {
                newSettings[input.name] = input.type === 'checkbox' ? input.checked : input.value;
            }
        });

        return newSettings;
    };

    // --- Логика сохранения через REST API ---
    const saveButton = document.getElementById('usp-save-tab-builder');
    if (saveButton) {
        saveButton.addEventListener('click', async (e) => {
            e.preventDefault();
            const originalButtonText = saveButton.textContent;
            saveButton.textContent = l10n.saving;
            saveButton.disabled = true;

            const config = serializeBuilder();
            const formData = new FormData();
            formData.append('config', JSON.stringify(config));

            try {
                const json = await window.UspCore.api.post('/tabs-config/update', formData);
                window.UspCore.ui.showAdminNotice(json.message, 'success', '#usp-tab-builder-notifications');
            } catch (error) {
                window.UspCore.ui.showAdminNotice(error.message || l10n.unknownError, 'error', '#usp-tab-builder-notifications');
            } finally {
                saveButton.textContent = originalButtonText;
                saveButton.disabled = false;
            }
        });
    }

    /**
     * Сериализует текущее состояние конструктора в массив объектов.
     * @returns {Array}
     */
    const serializeBuilder = () => {
        const finalConfig = [];

        const processTab = (tabEl, parentId = null, location = null) => {
            const config = JSON.parse(tabEl.dataset.config || '{}');
            const tabId = tabEl.dataset.id;

            // Обновляем родителя и местоположение
            config.parentId = parentId;
            if (location) {
                config.location = location;
            }

            // Обновляем порядок
            const siblings = Array.from(tabEl.parentNode.children);
            config.order = siblings.indexOf(tabEl) * 10;

            // Рекурсивно обрабатываем подвкладки
            const subTabsContainer = tabEl.querySelector('[data-sortable="subtabs"]');
            if (subTabsContainer) {
                const subTabs = subTabsContainer.querySelectorAll(':scope > .usp-tab-builder-tab');
                subTabs.forEach(subTabEl => {
                    processTab(subTabEl, tabId, config.location);
                });
            }

            // Удаляем subTabs из конфига, т.к. они будут плоским списком с parentId
            delete config.subTabs;
            finalConfig.push(config);
        };

        const locations = builder.querySelectorAll('.usp-tab-builder-location');
        locations.forEach(locationEl => {
            const locationId = locationEl.dataset.locationId;
            const topLevelTabs = locationEl.querySelectorAll(':scope > .usp-tab-builder-tabs > .usp-tab-builder-tab');
            topLevelTabs.forEach(tabEl => {
                processTab(tabEl, null, locationId);
            });
        });

        return finalConfig;
    };

    // Первичная инициализация
    initSortable();
});