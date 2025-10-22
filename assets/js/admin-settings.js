document.addEventListener('DOMContentLoaded', function () {
    if (!window.UspCore) {
        return;
    }

    /**
     * Инициализирует логику зависимых полей на странице настроек.
     */
    function initDependentFields() {
        const formWrapper = document.getElementById('usp-settings-form-wrapper');
        if (!formWrapper) return;

        const dependentFields = formWrapper.querySelectorAll('.usp-dependent-field-wrapper');
        if (dependentFields.length === 0) return;

        const parentFieldNames = new Set();
        dependentFields.forEach(field => {
            parentFieldNames.add(field.dataset.dependencyParent);
        });

        /**
         * Переключает видимость зависимых полей на основе значения родительского поля.
         * @param {HTMLElement} parentField - Родительское поле (input, select).
         */
        function toggleDependentFields(parentField) {
            const parentFieldName = parentField.getAttribute('name');
            let parentValue;

            if (parentField.tagName === 'SELECT') {
                parentValue = parentField.value;
            } else if (parentField.type === 'radio') {
                const checkedRadio = formWrapper.querySelector(`input[name="${parentFieldName}"]:checked`);
                parentValue = checkedRadio ? checkedRadio.value : null;
            } else if (parentField.type === 'checkbox') {
                // Для BooleanField (одиночный чекбокс)
                parentValue = parentField.checked ? '1' : '0';
            } else {
                parentValue = parentField.value;
            }

            dependentFields.forEach(dependentWrapper => {
                if (dependentWrapper.dataset.dependencyParent === parentFieldName) {
                    const requiredValue = JSON.parse(dependentWrapper.dataset.dependencyValue);
                    let shouldShow = false;

                    // Для BooleanField значение в data-атрибуте будет true/false, а значение поля '1'/'0'
                    if (typeof requiredValue === 'boolean') {
                        shouldShow = (parentValue === '1') === requiredValue;
                    } else {
                        const requiredValues = Array.isArray(requiredValue) ? requiredValue : [requiredValue];
                        shouldShow = requiredValues.includes(parentValue);
                    }

                    if (shouldShow) {
                        dependentWrapper.classList.remove('is-hidden-by-dependency');
                    } else {
                        dependentWrapper.classList.add('is-hidden-by-dependency');
                    }
                }
            });
        }

        // Первоначальная проверка и установка обработчиков
        parentFieldNames.forEach(fieldName => {
            const parentElements = formWrapper.querySelectorAll(`[name="${fieldName}"], [name="${fieldName}[]"]`);
            if (parentElements.length > 0) {
                // Инициализация при загрузке страницы
                toggleDependentFields(parentElements[0]);

                // Установка обработчиков событий
                parentElements.forEach(el => {
                    el.addEventListener('change', () => toggleDependentFields(el));
                });
            }
        });
    }

    // Инициализируем табы, используя наш новый хелпер
    window.UspCore.ui.initTabs('.usp-settings-wrap');

    // Инициализируем логику зависимых полей
    initDependentFields();

    // --- Логика сохранения настроек через REST API ---
    const saveButton = document.getElementById('usp-save-settings');
    if (saveButton) {
        saveButton.addEventListener('click', async (e) => {
            e.preventDefault();
            const l10n = window.uspL10n?.adminSettings || {};
            const originalButtonText = saveButton.textContent;
            saveButton.textContent = l10n.saving || 'Saving...';
            saveButton.disabled = true;

            const formWrapper = document.getElementById('usp-settings-form-wrapper');
            const inputs = formWrapper.querySelectorAll('input, select, textarea');
            const settingsData = {};

            inputs.forEach(input => {
                if (input.name.endsWith('[]')) {
                    // Пропускаем, так как обработаем ниже
                } else if (input.type === 'checkbox') {
                    // Обработка одиночных чекбоксов (BooleanField)
                    settingsData[input.name] = input.checked ? '1' : '0';
                } else if (input.type === 'radio') {
                    if (input.checked) {
                        settingsData[input.name] = input.value;
                    }
                } else {
                    settingsData[input.name] = input.value;
                }
            });

            // Отдельно обрабатываем группы чекбоксов, чтобы корректно собрать массив
            const checkboxGroups = formWrapper.querySelectorAll('input[name$="[]"]');
            const groupedData = {};

            checkboxGroups.forEach(checkbox => {
                const name = checkbox.name.slice(0, -2);
                if (!groupedData[name]) {
                    groupedData[name] = [];
                }
                if (checkbox.checked) {
                    groupedData[name].push(checkbox.value);
                }
            });

            // Объединяем с основными данными
            Object.assign(settingsData, groupedData);

            try {
                const json = await window.UspCore.api.post('/admin/settings', settingsData);
                window.UspCore.ui.showAdminNotice(json.message, 'success', '#usp-settings-notifications');
            } catch (error) {
                window.UspCore.ui.showAdminNotice(error.message || l10n.networkError || 'Network error occurred.', 'error', '#usp-settings-notifications');
            } finally {
                saveButton.textContent = originalButtonText;
                saveButton.disabled = false;
            }
        });
    }
});