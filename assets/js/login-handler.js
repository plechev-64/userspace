/**
 * Инициализирует обработчик для формы входа.
 * @param {HTMLFormElement} formElement - Элемент формы для инициализации.
 */
function initLoginHandler(formElement) {
    if (!formElement || !window.UspCore) {
        return;
    }

    // Предотвращаем повторную инициализацию
    if (formElement.dataset.uspLoginInitialized) {
        return;
    }
    formElement.dataset.uspLoginInitialized = 'true';

    formElement.addEventListener('submit', async function (e) {
        e.preventDefault();

        const l10n = window.uspL10n?.login || {};
        const submitButton = formElement.querySelector('button[type="submit"], input[type="submit"]');
        const originalButtonText = submitButton.innerHTML || submitButton.value;
        submitButton.innerHTML = l10n.loggingIn || 'Logging in...';
        submitButton.disabled = true;

        // Удаляем старые уведомления
        const oldNotices = formElement.parentElement.querySelectorAll('.usp-notice');
        oldNotices.forEach(notice => notice.remove());

        const formData = new FormData(formElement);

        try {
            const json = await window.UspCore.api.post('/login', formData);

            window.UspCore.ui.showFrontendNotice(json.message, 'success', formElement);

            // Перенаправляем пользователя после успешного входа
            if (json.redirect_url) {
                window.location.href = json.redirect_url;
            }
        } catch (error) {
            window.UspCore.ui.showFrontendNotice(error.message, 'error', formElement);
        } finally {
            submitButton.innerHTML = originalButtonText;
            submitButton.disabled = false;
        }
    });
}

// 1. Инициализация для форм, которые уже есть на странице при загрузке.
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form[data-usp-form="login"]').forEach(initLoginHandler);
});

// 2. Инициализация для форм, загруженных динамически через REST.
document.addEventListener('usp:tabContentLoaded', function (event) {
    event.detail.pane.querySelectorAll('form[data-usp-form="login"]').forEach(initLoginHandler);
});