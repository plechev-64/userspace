/**
 * Инициализирует обработчик для формы регистрации.
 * @param {HTMLFormElement} formElement - Элемент формы для инициализации.
 */
function initRegistrationHandler(formElement) {
    if (!formElement || !window.UspCore) {
        return;
    }

    // Предотвращаем повторную инициализацию
    if (formElement.dataset.uspRegistrationInitialized) {
        return;
    }
    formElement.dataset.uspRegistrationInitialized = 'true';

    formElement.addEventListener('submit', async function (e) {
        e.preventDefault();

        const l10n = window.uspL10n?.registration || {};
        const submitButton = formElement.querySelector('button[type="submit"], input[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        submitButton.innerHTML = l10n.registering || 'Registering...';
        submitButton.disabled = true;

        // Удаляем старые уведомления
        const oldNotices = formElement.parentElement.querySelectorAll('.usp-notice');
        oldNotices.forEach(notice => notice.remove());

        const formData = new FormData(formElement);

        try {
            const json = await window.UspCore.api.post('/register', formData);

            window.UspCore.ui.showFrontendNotice(json.message, 'success', formElement);
            formElement.reset(); // Очищаем форму после успеха

        } catch (error) {
            let errorMessage = error.message;
            // Проверяем, есть ли в ошибке массив с деталями валидации
            if (error.data && error.data.errors && Array.isArray(error.data.errors)) {
                errorMessage = `<ul>${error.data.errors.map(e => `<li>${e}</li>`).join('')}</ul>`;
            }
            window.UspCore.ui.showFrontendNotice(errorMessage, 'error', formElement);
        } finally {
            submitButton.innerHTML = originalButtonText;
            submitButton.disabled = false;
        }
    });
}

// 1. Инициализация для форм, которые уже есть на странице при загрузке.
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form[data-usp-form="registration"]').forEach(initRegistrationHandler);
});

// 2. Инициализация для форм, загруженных динамически через REST.
document.addEventListener('usp:tabContentLoaded', function (event) {
    event.detail.pane.querySelectorAll('form[data-usp-form="registration"]').forEach(initRegistrationHandler);
});