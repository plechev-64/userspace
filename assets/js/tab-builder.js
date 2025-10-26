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
        create: 'Create',
        addNewTab: 'Add New Tab',
        newTabIdLabel: 'ID (unique, a-z, 0-9, _)',
        newTabTitleLabel: 'Title',
        loading: 'Loading...',
        settingsLoadError: 'Error loading settings',
    };

    // --- Инициализация SortableJS ---
    const initSortable = () => {
        const itemContainers = builder.querySelectorAll('[data-sortable="tabs"], [data-sortable="subitems"]');
        itemContainers.forEach(container => {
            new Sortable(container, {
                animation: 150,
                handle: '.usp-tab-builder-item-header',
                group: 'shared-items', // Все элементы в одной группе для перемещения между локациями
                onEnd: () => {
                    // После любого перемещения обновляем состояние всех элементов
                    updateItemStates();
                },
                onMove: (evt) => {
                    // evt.to - контейнер, КУДА перемещаем
                    // evt.dragged - элемент, КОТОРЫЙ перемещаем

                    // Если мы пытаемся переместить вкладку в контейнер для подвкладок
                    if (evt.to.matches('[data-sortable="subitems"]')) {
                        // Запрещаем перемещать вкладку, у которой уже есть свои дочерние вкладки.
                        // Это предотвращает создание 3-го уровня вложенности.
                        const draggedSubItems = evt.dragged.querySelector('[data-sortable="subitems"]');
                        if (draggedSubItems && draggedSubItems.children.length > 0) {
                            return false; // Отменить перемещение
                        }
                    }
                    return true; // Разрешить перемещение во всех остальных случаях
                }
            });
        });
    };

    /**
     * Обновляет CSS-классы элементов в зависимости от их уровня вложенности.
     * Скрывает возможность добавления дочерних элементов для вложенных элементов.
     */
    const updateItemStates = () => {
        const allItems = builder.querySelectorAll('.usp-tab-builder-item');
        allItems.forEach(item => {
            // Проверяем, является ли вкладка вложенной (2-й уровень)
            if (item.closest('[data-sortable="subitems"]')) {
                item.classList.add('is-nested');
            } else {
                item.classList.remove('is-nested');
            }
        });
    };

    // --- Логика редактирования (пока заглушка) ---
    builder.addEventListener('click', async function (e) {
        const target = e.target.closest('[data-action="edit-tab"]');
        if (!target) {
            return;
        }
        e.preventDefault();
        const itemEl = target.closest('.usp-tab-builder-item');
        await openEditModal(itemEl);
    });

    // --- Логика создания новой вкладки ---
    const createButton = document.getElementById('usp-create-new-tab');
    if (createButton) {
        createButton.addEventListener('click', (e) => {
            e.preventDefault();
            openCreateModal();
        });
    }

    const openEditModal = async (itemEl) => {
        const currentConfig = itemEl.dataset.config;

        const formData = new FormData();
        formData.append('tabConfig', currentConfig);

        try {
            const json = await window.UspCore.api.post('/location/item/settings', formData);
            buildAndShowEditModal(itemEl, json.html);
        } catch (error) {
            alert((l10n.errorPrefix || 'Error: ') + error.message);
        }
    };

    const buildAndShowEditModal = (itemEl, settingsHtml) => {
        const config = JSON.parse(itemEl.dataset.config || '{}');

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
                const oldConfig = JSON.parse(itemEl.dataset.config || '{}');

                // Обновляем только те свойства, которые есть в форме
                const newConfig = {...oldConfig, ...newSettings};

                itemEl.dataset.config = JSON.stringify(newConfig);

                // Обновляем видимые элементы в конструкторе
                const titleEl = itemEl.querySelector('.tab-title');
                if (titleEl) {
                    titleEl.textContent = newConfig.title;
                }
                const iconEl = itemEl.querySelector('.dashicons');
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

    const openCreateModal = () => {
        const modalHtml = `
            <div class="usp-modal-backdrop is-visible" id="usp-create-tab-modal">
                <div class="usp-modal-content">
                    <div class="usp-modal-header">
                        <h2>${l10n.addNewTab}</h2>
                        <button type="button" class="usp-modal-close" aria-label="${l10n.close}">&times;</button>
                    </div>
                    <div class="usp-modal-body">
                        <div class="usp-form">
                            <div class="usp-form-group">
                                <label for="new-tab-id">${l10n.newTabIdLabel}</label>
                                <input type="text" id="new-tab-id" name="id" required pattern="[a-z0-9_]+">
                                <p class="description"></p>
                            </div>
                            <div class="usp-form-group">
                                <label for="new-tab-title">${l10n.newTabTitleLabel}</label>
                                <input type="text" id="new-tab-title" name="title" required>
                            </div>
                        </div>
                    </div>
                    <div class="usp-modal-footer">
                        <button type="button" class="button" data-action="cancel-create">${l10n.cancel}</button>
                        <button type="button" class="button button-primary" data-action="save-create">${l10n.create}</button>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        const modal = document.getElementById('usp-create-tab-modal');
        const idInput = modal.querySelector('#new-tab-id');
        const titleInput = modal.querySelector('#new-tab-title');
        const descriptionEl = idInput.nextElementSibling;

        modal.addEventListener('click', (e) => {
            if (e.target.matches('.usp-modal-close') || e.target.matches('[data-action="cancel-create"]')) {
                modal.remove();
            }

            if (e.target.matches('[data-action="save-create"]')) {
                const id = idInput.value.trim();
                const title = titleInput.value.trim();

                // Валидация
                if (!id || !title || !/^[a-z0-9_]+$/.test(id)) {
                    descriptionEl.textContent = 'ID is required and can only contain lowercase letters, numbers, and underscores.';
                    descriptionEl.style.color = 'red';
                    return;
                }
                if (document.querySelector(`[data-id="${id}"]`)) {
                    descriptionEl.textContent = 'This ID is already in use.';
                    descriptionEl.style.color = 'red';
                    return;
                }

                createNewTabElement({id, title});
                modal.remove();
            }
        });
    };

    const createNewTabElement = (config) => {
        const firstLocation = builder.querySelector('.usp-tab-builder-items[data-sortable="tabs"]');
        if (!firstLocation) {
            console.error('No tab locations found to add the new tab.');
            return;
        }

        const defaultConfig = {
            id: config.id,
            title: config.title,
            location: firstLocation.closest('.usp-tab-builder-location').dataset.locationId,
            order: 1000, // Помещаем в конец
            parentId: null,
            isPrivate: false,
            capability: 'read',
            icon: 'dashicons-admin-page',
            contentType: 'rest',
            itemType: 'tab', // Новые элементы по умолчанию - вкладки
            class: 'UserSpace\\Tabs\\CustomTab' // Указываем класс для кастомной вкладки
        };

        const tabHtml = `
            <div class="usp-tab-builder-item usp-tab-builder-item--tab" data-id="${defaultConfig.id}" data-config='${JSON.stringify(defaultConfig)}'>
                <div class="usp-tab-builder-item-header">
                    <span class="dashicons ${defaultConfig.icon}"></span>
                    <span class="tab-title">${defaultConfig.title}</span>
                    <div class="usp-tab-builder-item-actions">
                        <button type="button" class="button button-small" data-action="edit-tab">${l10n.edit || 'Edit'}</button>
                    </div>
                </div>
                <div class="usp-tab-builder-sub-items" data-sortable="subitems"></div>
            </div>
        `;

        firstLocation.insertAdjacentHTML('beforeend', tabHtml);

        // Re-initialize sortable for the new element's parent container if needed,
        // but since we add to an existing one, it should be fine.
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
                const json = await window.UspCore.api.post('/location/config/update', formData);
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

        const processItem = (itemEl, parentId = null, location = null) => {
            const config = JSON.parse(itemEl.dataset.config || '{}');
            const itemId = itemEl.dataset.id;

            // Обновляем родителя и местоположение
            config.parentId = parentId;
            if (location) {
                config.location = location;
            }

            // Обновляем порядок
            const siblings = Array.from(itemEl.parentNode.children);
            config.order = siblings.indexOf(itemEl) * 10;

            // Поле 'class' уже должно быть в объекте config, так как оно берется из data-config.

            // Рекурсивно обрабатываем подвкладки
            const subItemsContainer = itemEl.querySelector('[data-sortable="subitems"]');
            if (subItemsContainer) {
                const subItems = subItemsContainer.querySelectorAll(':scope > .usp-tab-builder-item');
                subItems.forEach(subItemEl => {
                    processItem(subItemEl, itemId, config.location);
                });
            }

            // Удаляем subTabs из конфига, т.к. они будут плоским списком с parentId
            delete config.subTabs;
            finalConfig.push(config);
        };

        const locations = builder.querySelectorAll('.usp-tab-builder-location');
        locations.forEach(locationEl => {
            const locationId = locationEl.dataset.locationId;
            const topLevelItems = locationEl.querySelectorAll(':scope > .usp-tab-builder-items > .usp-tab-builder-item');
            topLevelItems.forEach(itemEl => {
                processItem(itemEl, null, locationId);
            });
        });

        return finalConfig;
    };

    // Первичная инициализация
    initSortable();
    updateItemStates(); // Устанавливаем начальное состояние
});