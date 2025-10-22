document.addEventListener('DOMContentLoaded', function () {
    if (!window.UspCore) {
        return;
    }

    /**
     * Сериализует форму в JavaScript-объект, корректно обрабатывая
     * поля с множественными значениями (name="field[]") и одиночные чекбоксы.
     * @param {HTMLFormElement} formElement - Элемент формы или обертка, содержащая поля.
     * @returns {Object}
     */
    function serializeForm(formElement) {
        const data = {};
        const formData = new FormData();

        // 1. Вручную находим все поля и добавляем их в FormData,
        // чтобы корректно обработать div как форму.
        formElement.querySelectorAll('input, select, textarea').forEach(field => {
            if (field.type === 'checkbox' || field.type === 'radio') {
                if (field.checked) {
                    formData.append(field.name, field.value);
                }
            } else {
                // Пропускаем файловые инпуты, чтобы не отправлять сами файлы
                if (field.type !== 'file') {
                    formData.append(field.name, field.value);
                }
            }
        });

        // 2. Инициализируем все одиночные чекбоксы значением '0',
        // чтобы неотмеченные тоже отправлялись на сервер.
        formElement.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            if (!checkbox.name.endsWith('[]')) {
                data[checkbox.name] = '0';
            }
        });

        // 3. Заполняем объект данными из FormData, корректно создавая массивы.
        for (const [key, value] of formData.entries()) {
            if (key.endsWith('[]')) {
                const cleanKey = key.slice(0, -2);
                // Если массив для этого ключа еще не создан, создаем его.
                if (!data[cleanKey]) {
                    data[cleanKey] = [];
                }
                data[cleanKey].push(value);
            } else {
                data[key] = value;
            }
        }
        return data;
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

            const formWrapper = document.getElementById('usp-settings-form-wrapper'); // Наша "форма"
            const settingsData = serializeForm(formWrapper); // Используем новую универсальную функцию

            try {
                // Отправляем данные на сервер
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