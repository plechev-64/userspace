/**
 * Инициализирует один компонент загрузчика файлов.
 * @param {HTMLElement} uploader - DOM-элемент контейнера загрузчика (.usp-uploader).
 */
function initUploader(uploader) {
    if (!window.UspCore || uploader.dataset.uspUploaderInitialized) {
        return;
    }
    uploader.dataset.uspUploaderInitialized = 'true';

    const fileInput = uploader.querySelector('.usp-uploader-input');
    const statusEl = uploader.querySelector('.usp-uploader-status');
    const previewWrapper = uploader.querySelector('.usp-uploader-preview-wrapper');
    const isMultiple = uploader.dataset.multiple === 'true';

    if (!fileInput || !statusEl || !previewWrapper) {
        console.error('Uploader component is missing required elements.', uploader);
        return;
    }

    uploader.addEventListener('click', function (e) {
        const target = e.target;

        // Клик по кнопке "Select File"
        if (target.matches('.usp-upload-button')) {
            e.preventDefault();
            fileInput.click();
        }

        // Клик по кнопке "Remove" (для одиночного загрузчика)
        if (target.matches('.usp-remove-button') && !isMultiple) {
            e.preventDefault();
            uploader.classList.remove('has-file');
            previewWrapper.innerHTML = '';
            uploader.querySelector('.usp-uploader-value').value = '';
        }

        // Клик по кнопке "Remove" для отдельного элемента в галерее
        if (target.matches('.usp-remove-item-button')) {
            e.preventDefault();
            const item = target.closest('.usp-uploader-preview-item');
            if (item) {
                const idToRemove = item.dataset.id.toString();
                item.remove();
                updateHiddenInputValue(uploader, idToRemove, 'remove');
            }
        }
    });

    fileInput.addEventListener('change', function (e) {
        const files = e.target.files;
        if (!files.length) {
            return;
        }

        for (const file of files) {
            handleFileUpload(file, uploader);
        }
    });
}

/**
 * Обрабатывает загрузку одного файла.
 * @param {File} file
 * @param {HTMLElement} uploader
 */
function handleFileUpload(file, uploader) {
    const statusEl = uploader.querySelector('.usp-uploader-status');
    const previewWrapper = uploader.querySelector('.usp-uploader-preview-wrapper');
    const isMultiple = uploader.dataset.multiple === 'true';

    const uploaderService = window.UspCore.FileUploader(file, {
        validationRules: uploader.dataset,
        config: uploader.dataset.config,
        signature: uploader.dataset.signature,
        onProgress: (message) => {
            statusEl.textContent = message;
        },
        onSuccess: (json) => {
            const previewHtml = `
                <div class="usp-uploader-preview-item" data-id="${json.attachmentId}">
                    <img src="${json.previewUrl}" alt="${window.uspL10n?.uploader?.previewAlt || 'Preview'}">
                    <button type="button" class="usp-remove-item-button" aria-label="${window.uspL10n?.uploader?.remove || 'Remove'}">&times;</button>
                </div>`;

            if (isMultiple) {
                previewWrapper.insertAdjacentHTML('beforeend', previewHtml);
                updateHiddenInputValue(uploader, json.attachmentId, 'add');
            } else {
                previewWrapper.innerHTML = previewHtml;
                uploader.querySelector('.usp-uploader-value').value = json.attachmentId;
            }

            // Показываем кнопку "Remove", если ее еще нет
            const removeButton = uploader.querySelector('.usp-remove-button');
            if (!removeButton) {
                const actionsWrapper = uploader.querySelector('.usp-uploader-actions');
                actionsWrapper.insertAdjacentHTML('beforeend', `<button type="button" class="button button-link-delete usp-remove-button">${window.uspL10n?.uploader?.remove || 'Remove'}</button>`);
            }
            uploader.classList.add('has-file');
        },
        onError: (error) => {
            // The error message is already set in onProgress, but we can also alert it.
            alert(error.message);
        },
        onFinally: () => {
            uploader.classList.remove('is-uploading');
            setTimeout(() => { statusEl.textContent = ''; }, 3000);
        }
    });

    uploader.classList.add('is-uploading');
    uploaderService.process();
}

/**
 * Обновляет значение скрытого поля для галереи.
 * @param {HTMLElement} uploader
 * @param {string} id
 * @param {'add'|'remove'} action
 */
function updateHiddenInputValue(uploader, id, action) {
    const valueInput = uploader.querySelector('.usp-uploader-value');
    let currentIds = valueInput.value ? valueInput.value.split(',') : [];

    if (action === 'add') {
        currentIds.push(id);
    } else if (action === 'remove') {
        currentIds = currentIds.filter(currentId => currentId !== id.toString());
    }

    valueInput.value = currentIds.join(',');

    if (currentIds.length === 0) {
        uploader.classList.remove('has-file');
    }
}

// 1. Инициализация для загрузчиков, которые уже есть на странице.
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.usp-uploader').forEach(initUploader);
});

// 2. Инициализация для загрузчиков, добавленных динамически.
document.addEventListener('usp:tabContentLoaded', function (event) {
    event.detail.pane.querySelectorAll('.usp-uploader').forEach(initUploader);
});