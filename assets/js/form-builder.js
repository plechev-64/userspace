document.addEventListener('DOMContentLoaded', function () {
    const builder = document.querySelector('[data-usp-form-builder]');
    const l10n = window.uspL10n?.formBuilder || {};
    let fieldsToDelete = new Set(); // Используем Set для хранения уникальных имен полей для удаления

    if (!builder || !window.UspCore) {
        return;
    }

    // --- Инициализация SortableJS ---
    const initSortable = () => {
        const sectionContainer = builder.querySelector('.usp-form-builder-sections');
        if (sectionContainer) {
            new Sortable(sectionContainer, {
                animation: 150,
                handle: '.usp-form-builder-section-header',
                group: 'sections',
            });
        }

        const blockContainers = builder.querySelectorAll('.usp-form-builder-blocks');
        blockContainers.forEach(container => {
            new Sortable(container, {
                animation: 150,
                handle: '.usp-form-builder-block-header',
                group: 'blocks',
            });
        });

        const fieldContainers = builder.querySelectorAll('.usp-form-builder-fields');
        fieldContainers.forEach(container => {
            new Sortable(container, {
                animation: 150,
                group: {
                    name: 'fields',
                    pull: true,
                    put: true,
                },
            });
        });
    };

    /**
     * Создает HTML-элемент из шаблона.
     * @param {string} templateId - ID тега <template>.
     * @param {object} replacements - Объект с заменами плейсхолдеров.
     * @returns {HTMLElement|null}
     */
    const getTemplate = (templateId, replacements = {}) => {
        const template = document.getElementById(templateId);
        if (!template) {
            console.error(`Template with id ${templateId} not found.`);
            return null;
        }
        let html = template.innerHTML;
        for (const key in replacements) {
            html = html.replace(new RegExp(key, 'g'), replacements[key]);
        }
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html.trim();
        return tempDiv.firstChild;
    };

    /**
     * Кодирует HTML-сущности для безопасного хранения в data-атрибуте.
     * @param {string} str
     * @returns {string}
     */
    const encodeHtmlEntities = (str) => {
        return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    };

    /**
     * Декодирует HTML-сущности перед JSON.parse.
     * @param {string} str
     * @returns {string}
     */
    const decodeHtmlEntities = (str) => {
        const textarea = document.createElement('textarea');
        textarea.innerHTML = str;
        return textarea.value;
    };

    // --- Логика добавления элементов ---
    builder.addEventListener('click', function (e) {
        const target = e.target.closest('[data-action]');
        if (!target) {
            return;
        }

        const action = target.dataset.action;

        if ('add-section' === action) {
            e.preventDefault();
            addSection();
        }
        if ('add-block' === action) {
            e.preventDefault();
            const section = target.closest('.usp-form-builder-section');
            addBlock(section);
        }
        if ('delete-section' === action) {
            e.preventDefault();
            deleteSection(target.closest('.usp-form-builder-section'));
        }
        if ('delete-block' === action) {
            e.preventDefault();
            deleteBlock(target.closest('.usp-form-builder-block'));
        }
        if ('delete-field' === action) {
            e.preventDefault();
            moveFieldToAvailable(target.closest('.usp-form-builder-field'));
        }
        if ('edit-field' === action) {
            e.preventDefault();
            openEditModal(target.closest('.usp-form-builder-field'));
        }
        if ('add-custom-field' === action) {
            e.preventDefault();
            const block = target.closest('.usp-form-builder-block');
            openCreateModal(block);
        }
    });

    const addSection = () => {
        const sectionContainer = builder.querySelector('.usp-form-builder-sections');
        const newSection = getTemplate('usp-template-section', {
            '__SECTION_ID__': 'section-' + Date.now(),
            '__SECTION_TITLE__': '' // Оставляем пустым, чтобы сработал placeholder
        });
        sectionContainer.appendChild(newSection);
        initSortable(); // Re-initialize for new containers
    };

    const addBlock = (section) => {
        const blockContainer = section.querySelector('.usp-form-builder-blocks');
        const newBlock = getTemplate('usp-template-block', {
            '__BLOCK_ID__': 'block-' + Date.now(),
            '__BLOCK_TITLE__': '' // Оставляем пустым, чтобы сработал placeholder
        });
        blockContainer.appendChild(newBlock);
        initSortable(); // Re-initialize for new containers
    };

    // --- Логика удаления и перемещения элементов ---
    const deleteSection = (sectionEl) => {
        if (!sectionEl) return;
        const fields = sectionEl.querySelectorAll('.usp-form-builder-field');
        const customFields = sectionEl.querySelectorAll('.usp-form-builder-field[data-is-custom="true"]');
        let confirmed = true;

        if (customFields.length > 0) {
            confirmed = confirm(l10n.confirmDeleteSectionWithCustom || 'This section contains custom fields. Deleting the section will permanently remove all data for these fields from all users. Are you sure?');
            if (confirmed) {
                customFields.forEach(field => fieldsToDelete.add(field.dataset.name));
            }
        } else if (fields.length > 0) {
            confirmed = confirm(l10n.confirmMoveFields || 'This section contains fields. They will be moved to "Available Fields". Are you sure?');
        }

        if (confirmed) {
            sectionEl.remove();
        }
    };

    const deleteBlock = (blockEl) => {
        if (!blockEl) return;
        const fields = blockEl.querySelectorAll('.usp-form-builder-field');
        const customFields = blockEl.querySelectorAll('.usp-form-builder-field[data-is-custom="true"]');
        let confirmed = true;

        if (customFields.length > 0) {
            confirmed = confirm(l10n.confirmDeleteBlockWithCustom || 'This block contains custom fields. Deleting the block will permanently remove all data for these fields from all users. Are you sure?');
            if (confirmed) {
                customFields.forEach(field => fieldsToDelete.add(field.dataset.name));
            }
        } else if (fields.length > 0) {
            confirmed = confirm(l10n.confirmMoveFields || 'This block contains fields. They will be moved to "Available Fields". Are you sure?');
        }

        if (confirmed) {
            blockEl.remove();
        }
    };

    const moveFieldToAvailable = (fieldEl) => {
        if (!fieldEl) return;

        // Кастомные поля удаляются навсегда, а не перемещаются
        if (fieldEl.dataset.isCustom === 'true') {
            deleteCustomField(fieldEl);
            return;
        }

        const availableContainer = builder.querySelector('.usp-form-builder-available-fields .usp-form-builder-fields');
        if (availableContainer) {
            availableContainer.appendChild(fieldEl);
        } else {
            // Если по какой-то причине панели нет, просто удаляем поле
            fieldEl.remove();
        }
    };

    const deleteCustomField = (fieldEl) => {
        if (confirm(l10n.confirmDeleteCustomField || 'This is a custom field. Deleting it will permanently remove all its data from all users. Are you sure?')) {
            fieldsToDelete.add(fieldEl.dataset.name);
            fieldEl.remove();
        }
    };

    // --- Логика редактирования полей ---
    const openEditModal = async (fieldEl) => {
        const fieldType = fieldEl.dataset.type;
        const currentConfig = fieldEl.dataset.config;

        // 1. Запросить HTML формы настроек с сервера
        const formData = new FormData();
        formData.append('action', 'usp_get_field_settings_form');
        formData.append('nonce', uspApiSettings.nonce);
        formData.append('fieldType', fieldType);
        formData.append('fieldConfig', currentConfig);

        try {
            const json = await window.UspCore.api.post('/field-settings/settings', formData);
            // Успешная загрузка, строим модальное окно
            buildAndShowEditModal(fieldEl, currentConfig, json.html);
        } catch (error) {
            alert((l10n.errorPrefix || 'Error: ') + error.message);
        }
    };

    const buildAndShowEditModal = (fieldEl, currentConfig, settingsHtml) => {
        // 2. Создать и показать модальное окно с полученным HTML
        const modalHtml = `
            <div class="usp-modal-backdrop">
                <div class="usp-modal-content">
                    <div class="usp-modal-header">
                        <h2>${(l10n.fieldSettingsTitle || 'Field Settings: ')} ${JSON.parse(currentConfig).label || fieldEl.dataset.name}</h2>
                        <button type="button" class="usp-modal-close" aria-label="${l10n.close || 'Close'}">&times;</button>
                    </div>
                    <div class="usp-modal-body">
                        <div class="usp-form-group">
                            <label for="field-setting-name">${l10n.fieldNameLabel || 'Name (read-only)'}</label>
                            <input type="text" id="field-setting-name" value="${fieldEl.dataset.name}" readonly disabled>
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

        // 3. Обработчики для модального окна
        modal.addEventListener('click', (e) => {
            if (e.target.matches('.usp-modal-close') || e.target.matches('[data-action="cancel-edit"]')) {
                modal.remove();
            }

            if (e.target.matches('[data-action="save-edit"]')) {
                // 3.1. Собрать данные из формы настроек
                const newSettings = collectSettingsFromModal(modal);

                // 3.2. Обновить data-config на элементе в конструкторе
                const oldConfig = JSON.parse(fieldEl.dataset.config || '{}');
                const newConfig = { ...oldConfig };

                newConfig.label = newSettings.label || oldConfig.label;
                newConfig.options = newSettings.options || oldConfig.options;

                if (!newConfig.rules) newConfig.rules = {};
                newConfig.rules.required = !!newSettings.required;

                fieldEl.dataset.config = encodeHtmlEntities(JSON.stringify(newConfig));
                fieldEl.querySelector('.field-label').textContent = newConfig.label || fieldEl.dataset.name;

                modal.remove();
            }
        });
    };

    // --- Логика создания нового поля ---
    const openCreateModal = (blockEl) => {
        const defaultName = 'custom_field_' + Date.now();

        let fieldTypesOptions = '';
        for (const type in uspApiSettings.fieldTypes) {
            // Исключаем служебные типы, если они есть
            if (type !== 'key_value_editor') {
                fieldTypesOptions += `<option value="${type}">${type}</option>`;
            }
        }

        const modalHtml = `
            <div class="usp-modal-backdrop">
                <div class="usp-modal-content">
                    <div class="usp-modal-header">
                        <h2>${l10n.createFieldTitle || 'Create New Field'}</h2>
                        <button type="button" class="usp-modal-close" aria-label="${l10n.close || 'Close'}">&times;</button>
                    </div>
                    <div class="usp-modal-body">
                        <div class="usp-form-group">
                            <label for="new-field-name">${l10n.newFieldNameLabel || 'Name (unique identifier)'}</label>
                            <input type="text" id="new-field-name" value="${defaultName}">
                        </div>
                        <div class="usp-form-group">
                            <label for="new-field-type">${l10n.newFieldTypeLabel || 'Field Type'}</label>
                            <select id="new-field-type">
                                <option value="">${l10n.selectType || '-- Select type --'}</option>
                                ${fieldTypesOptions}
                            </select>
                        </div>
                        <div id="new-field-settings-container"></div>
                    </div>
                    <div class="usp-modal-footer">
                        <button type="button" class="button" data-action="cancel-create">${l10n.cancel || 'Cancel'}</button>
                        <button type="button" class="button button-primary" data-action="confirm-create" disabled>${l10n.createFieldButton || 'Create Field'}</button>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        const modal = document.querySelector('.usp-modal-backdrop');
        const typeSelect = modal.querySelector('#new-field-type');
        const settingsContainer = modal.querySelector('#new-field-settings-container');
        const createButton = modal.querySelector('[data-action="confirm-create"]');

        // Загрузка настроек при выборе типа
        typeSelect.addEventListener('change', async () => {
            const selectedType = typeSelect.value;
            settingsContainer.innerHTML = `<p>${l10n.loading || 'Loading...'}</p>`;
            createButton.disabled = true;

            if (!selectedType) {
                settingsContainer.innerHTML = '';
                return;
            }

            const formData = new FormData();
            formData.append('action', 'usp_get_field_settings_form');
            formData.append('nonce', uspApiSettings.nonce);
            formData.append('fieldType', selectedType);
            formData.append('fieldConfig', '{}'); // Пустой конфиг для нового поля

            try {
                const json = await window.UspCore.api.post('/field-settings/settings', formData);
                settingsContainer.innerHTML = json.html;
                createButton.disabled = false;
            } catch (error) {
                settingsContainer.innerHTML = `<p style="color: red;">${error.message || l10n.settingsLoadError || 'Error loading settings'}</p>`;
            }
        });

        // Обработка кнопок модального окна
        modal.addEventListener('click', (e) => {
            if (e.target.matches('.usp-modal-close') || e.target.matches('[data-action="cancel-create"]')) {
                modal.remove();
            }

            if (e.target.matches('[data-action="confirm-create"]')) {
                const newName = modal.querySelector('#new-field-name').value;
                const newType = modal.querySelector('#new-field-type').value;

                if (!newName || !newType) {
                    alert(l10n.nameAndTypeRequired || 'Field name and type are required.');
                    return;
                }

                const newConfig = collectSettingsFromModal(modal);
                newConfig.type = newType; // Добавляем тип в конфиг

                const newField = getTemplate('usp-template-field', {
                    '__FIELD_NAME__': newName,
                    '__FIELD_TYPE__': newType,
                    '__FIELD_CONFIG__': encodeHtmlEntities(JSON.stringify(newConfig)),
                    '__FIELD_LABEL__': newConfig.label || newName,
                });

                const fieldsContainer = blockEl.querySelector('.usp-form-builder-fields');
                fieldsContainer.appendChild(newField);
                modal.remove();
            }
        });
    };

    const collectSettingsFromModal = (modal) => {
        const newSettings = {};
        const inputs = modal.querySelectorAll('input[type="text"], input[type="checkbox"], select');

        inputs.forEach(input => {
            if (input.name) {
                newSettings[input.name] = input.type === 'checkbox' ? input.checked : input.value;
            }
        });

        // Handle KeyValueEditor separately
        const kvEditors = modal.querySelectorAll('.usp-kv-editor');
        kvEditors.forEach(editor => {
            const baseName = editor.dataset.kvEditorName;
            if (baseName) {
                newSettings[baseName] = {};
                const pairs = editor.querySelectorAll('.usp-kv-pair');
                pairs.forEach(pair => {
                    const key = pair.querySelector('.usp-kv-key').value;
                    const val = pair.querySelector('.usp-kv-value').value;
                    if (key) {
                        newSettings[baseName][key] = val;
                    }
                });
            }
        });

        return newSettings;
    };

    // Глобальный обработчик для динамически добавляемых элементов (внутри модальных окон)
    document.body.addEventListener('click', function(e) {
        // Добавление опции в Key-Value Editor
        if (e.target.matches('.usp-kv-add')) {
            e.preventDefault();
            const editor = e.target.closest('.usp-kv-editor');
            if (!editor) return;

            const container = editor.querySelector('.usp-kv-pairs');
            const editorName = editor.dataset.kvEditorName;
            const pairHtml = `
                <div class="usp-kv-pair">
                    <input type="text" name="${editorName}[keys][]" class="usp-kv-key" placeholder="${l10n.kvValuePlaceholder || 'Value'}">
                    <input type="text" name="${editorName}[values][]" class="usp-kv-value" placeholder="${l10n.kvLabelPlaceholder || 'Label'}">
                    <button type="button" class="button button-link-delete usp-kv-remove" aria-label="${l10n.remove || 'Remove'}">&times;</button>
                </div>`;
            container.insertAdjacentHTML('beforeend', pairHtml);
        }

        // Удаление опции из Key-Value Editor
        if (e.target.matches('.usp-kv-remove')) {
            e.preventDefault();
            e.target.closest('.usp-kv-pair').remove();
        }
    });

    // --- Логика сохранения через REST API ---
    const saveButton = document.getElementById('usp-save-form-builder');
    if (saveButton) {
        saveButton.addEventListener('click', async (e) => {
            e.preventDefault();
            const originalButtonText = saveButton.textContent;
            saveButton.textContent = l10n.saving || 'Saving...';
            saveButton.disabled = true;

            const config = serializeBuilder();
            const formData = new FormData();
            formData.append('deleted_fields', JSON.stringify(Array.from(fieldsToDelete)));
            formData.append('config', JSON.stringify(config));

            try {
                const json = await window.UspCore.api.post(`/admin/${uspApiSettings.formType}-form/config`, formData);
                window.UspCore.ui.showAdminNotice(json.message, 'success', '#usp-form-builder-notifications');
                fieldsToDelete.clear(); // Очищаем список после успешного сохранения
            } catch (error) {
                window.UspCore.ui.showAdminNotice(error.message || l10n.unknownError || 'Unknown error', 'error', '#usp-form-builder-notifications');
            } finally {
                saveButton.textContent = originalButtonText;
                saveButton.disabled = false;
            }
        });
    }

    const serializeBuilder = () => {
        const config = { sections: [] };
        const sections = builder.querySelectorAll('.usp-form-builder-sections > .usp-form-builder-section');

        sections.forEach(sectionEl => {
            const sectionData = {
                id:    sectionEl.dataset.id,
                title: sectionEl.querySelector('.usp-form-builder-section-title .title-input').value,
                blocks: []
            };

            sectionEl.querySelectorAll('.usp-form-builder-blocks > .usp-form-builder-block').forEach(blockEl => {
                const blockData = {
                    id:    blockEl.dataset.id,
                    title: blockEl.querySelector('.usp-form-builder-block-title .title-input').value,
                    fields: {}
                };

                blockEl.querySelectorAll('.usp-form-builder-fields > .usp-form-builder-field').forEach(fieldEl => {
                    const decodedConfig = decodeHtmlEntities(fieldEl.dataset.config || '{}');
                    blockData.fields[fieldEl.dataset.name] = JSON.parse(decodedConfig);
                });
                sectionData.blocks.push(blockData);
            });
            config.sections.push(sectionData);
        });
        return config;
    };

    // Первичная инициализация
    initSortable();
});