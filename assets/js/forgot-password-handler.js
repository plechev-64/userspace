document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form[data-usp-form="forgot-password"]');
    if (!form || !window.UspCore) {
        return;
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        
        const l10n = window.uspL10n?.forgotPassword || {};
        const submitButton = form.querySelector('button[type="submit"], input[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        submitButton.innerHTML = l10n.processing || 'Processing...';
        submitButton.disabled = true;

        const formData = new FormData(form);

        try {
            const json = await window.UspCore.api.post('/user/password/reset', formData);

            window.UspCore.ui.showFrontendNotice(json.message, 'success', form);
            form.reset();
        } catch (error) {
            window.UspCore.ui.showFrontendNotice(error.message, 'error', form);
        } finally {
            submitButton.innerHTML = originalButtonText;
            submitButton.disabled = false;
        }
    });
});