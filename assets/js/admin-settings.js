document.addEventListener('DOMContentLoaded', function () {
    if (!window.UspCore) {
        return;
    }

    // Инициализируем табы, используя наш новый хелпер
    window.UspCore.ui.initTabs('.usp-settings-wrap');

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