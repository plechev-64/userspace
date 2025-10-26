/**
 * Инициализирует один компонент загрузчика файлов.
 * @param {HTMLElement} uploader - DOM-элемент контейнера загрузчика (.usp-uploader).
 */

/**
 * Управляет скрытыми полями для множественной загрузки.
 * @param {HTMLElement} uploader - DOM-элемент контейнера загрузчика.
 * @param {string} fieldName - Базовое имя поля (например, 'files').
 * @param {string} id - ID файла.
 * @param {'add'|'remove'} action - Действие ('add' или 'remove').
 */
function manageMultipleHiddenInputs(uploader, fieldName, id, action) {
    if (action === 'add') {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = `${fieldName}[]`;
        input.value = id;
        input.classList.add('usp-uploader-managed-value'); // Добавляем класс для идентификации управляемых полей
        uploader.appendChild(input);
    } else if (action === 'remove') {
        const inputs = uploader.querySelectorAll(`input.usp-uploader-managed-value[name="${fieldName}[]"]`);
        for (const input of inputs) {
            if (input.value === id.toString()) {
                input.remove();
                break;
            }
        }
    }

    // Обновляем класс 'has-file' на основе наличия управляемых скрытых полей
    const remainingInputs = uploader.querySelectorAll(`input.usp-uploader-managed-value[name="${fieldName}[]"]`);
    if (remainingInputs.length > 0) {
        uploader.classList.add('has-file');
    } else {
        uploader.classList.remove('has-file');
    }
}

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
    const fieldName = uploader.dataset.fieldName; // Получаем базовое имя поля из data-атрибута

    if (!fileInput || !statusEl || !previewWrapper || !fieldName) {
        console.error('Uploader component is missing required elements or data-fieldName.', uploader);
        return;
    }

    // Обработка начальных значений для множественного загрузчика
    if (isMultiple) {
        const initialValueInput = uploader.querySelector('.usp-uploader-value');
        if (initialValueInput) {
            if (initialValueInput.value) {
                const initialIds = initialValueInput.value.split(',');
                initialIds.forEach(id => {
                    if (id) { // Убеждаемся, что ID не пустой
                        manageMultipleHiddenInputs(uploader, fieldName, id, 'add');
                    }
                });
            }
            initialValueInput.remove(); // Удаляем оригинальное скрытое поле
        }
        // Убеждаемся, что класс 'has-file' установлен корректно после инициализации
        const remainingInputs = uploader.querySelectorAll(`input.usp-uploader-managed-value[name="${fieldName}[]"]`);
        if (remainingInputs.length > 0) {
            uploader.classList.add('has-file');
        } else {
            uploader.classList.remove('has-file');
        }
    }

    uploader.addEventListener('click', async function (e) {
        const target = e.target;

        // Клик по кнопке "Select File"
        if (target.matches('.usp-upload-button')) {
            e.preventDefault();
            fileInput.click();
        }

        // Клик по кнопке "Remove" (для одиночного загрузчика)
        if (target.matches('.usp-remove-button') && !isMultiple) {
            e.preventDefault();
            // Для одиночного загрузчика, если кнопка "Remove" была нажата,
            // то класс 'has-file' будет удален, а значение очищено.
            // Если удаление происходит через usp-remove-item-button, то это будет обработано там.
            // uploader.classList.remove('has-file'); // Этот класс будет обновлен при очистке значения
            previewWrapper.innerHTML = '';
            uploader.querySelector('.usp-uploader-value').value = '';
        }

        // Клик по кнопке "Remove" для отдельного элемента в галерее
        if (target.matches('.usp-remove-item-button') && !target.disabled) {
            e.preventDefault();

            // Запрашиваем подтверждение у пользователя
            const confirmMessage = window.uspL10n?.uploader?.confirmDelete || 'Are you sure you want to delete this file permanently?';
            if (!confirm(confirmMessage)) {
                return;
            }

            const item = target.closest('.usp-uploader-preview-item');
            if (item) {
                const idToRemove = item.dataset.id.toString();
                target.disabled = true; // Блокируем кнопку на время запроса
                item.style.opacity = '0.5'; // Визуально показываем, что идет процесс

                try {
                    await window.UspCore.api.delete(`/media/${idToRemove}`);
                    item.remove(); // Удаляем элемент предпросмотра только после успешного ответа от сервера

                    if (isMultiple) {
                        manageMultipleHiddenInputs(uploader, fieldName, idToRemove, 'remove');
                    } else {
                        uploader.classList.remove('has-file');
                        uploader.querySelector('.usp-uploader-value').value = '';
                    }
                } catch (error) {
                    alert(error.message || 'Failed to delete file.');
                    target.disabled = false; // Разблокируем кнопку в случае ошибки
                    item.style.opacity = '1';
                }
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
    const isMultiple = uploader.dataset.multiple === 'true'; // Получаем isMultiple
    const fieldName = uploader.dataset.fieldName; // Получаем базовое имя поля

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
                // Для множественной загрузки, добавляем новый элемент предпросмотра и создаем отдельное скрытое поле
                previewWrapper.insertAdjacentHTML('beforeend', previewHtml);
                manageMultipleHiddenInputs(uploader, fieldName, json.attachmentId, 'add');
            } else {
                // Для одиночной загрузки, заменяем предпросмотр и обновляем значение единственного скрытого поля
                previewWrapper.innerHTML = previewHtml;
                uploader.querySelector('.usp-uploader-value').value = json.attachmentId;
            }

            uploader.classList.add('has-file');
        },
        onError: (error) => {
            alert(error.message);
        },
        onFinally: () => {
            uploader.classList.remove('is-uploading');
            setTimeout(() => {
                statusEl.textContent = '';
            }, 3000);
        }
    });

    uploader.classList.add('is-uploading');
    uploaderService.process();
}

// 1. Инициализация для загрузчиков, которые уже есть на странице.
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.usp-uploader').forEach(initUploader);
});

// 2. Инициализация для загрузчиков, добавленных динамически.
document.addEventListener('usp:tabContentLoaded', function (event) {
    event.detail.pane.querySelectorAll('.usp-uploader').forEach(initUploader);
});