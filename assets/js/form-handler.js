/**
 * Инициализирует обработчик отправки для указанной формы.
 * @param {HTMLFormElement} formElement - Элемент формы для инициализации.
 */
function initFormHandler(formElement) {
    if (!formElement || !window.UspCore) {
        return;
    }

    const actionUrl = formElement.dataset.uspAction;
    if (!actionUrl) {
        console.error('Form handler error: The form is missing the "data-usp-action" attribute with the submission URL.', formElement);
        return;
    }

    formElement.addEventListener('submit', async function (e) {
        e.preventDefault();
        
        const l10n = window.uspL10n?.formHandler || {};
        const submitButton = formElement.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        submitButton.innerHTML = l10n.saving || 'Saving...';
        submitButton.disabled = true;

        const oldNotices = formElement.closest('.usp-account-tab-pane, body').querySelectorAll('.usp-notice');
        oldNotices.forEach(notice => notice.remove());

        const formData = new FormData(formElement);

        try {
            const json = await window.UspCore.api.post(actionUrl, formData);
            window.UspCore.ui.showFrontendNotice(json.message, 'success', formElement);
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
    const staticForms = document.querySelectorAll('form[data-usp-form]');
    staticForms.forEach(initFormHandler);
});

// 2. Инициализация для форм, загруженных динамически через REST.
document.addEventListener('usp:tabContentLoaded', function (event) {
    const dynamicallyLoadedForm = event.detail.pane.querySelector('form[data-usp-form]');
    if (dynamicallyLoadedForm) {
        initFormHandler(dynamicallyLoadedForm);
    }
});