(function (window, document) {
    'use strict';

    /**
     * API-клиент для плагина UserSpace.
     */
    class ApiClient {
        constructor(settings) {
            if (!settings || !settings.root || !settings.nonce) {
                throw new Error('ApiClient: API settings are incomplete.');
            }
            this.settings = settings;
        }

        /**
         * Constructs the full URL for a given REST API endpoint.
         * @param {string} endpoint The relative path of the endpoint (e.g., '/sse/events').
         * @returns {string} The full URL.
         */
        getEndpointUrl(endpoint) {
            return `${this.settings.root}${this.settings.namespace}${endpoint}`;
        }

        async post(endpoint, body) {
            const apiUrl = `${this.settings.root}${this.settings.namespace}${endpoint}`;
            const headers = { 'X-WP-Nonce': this.settings.nonce };
            let requestBody = body;

            if (typeof body === 'object' && body !== null && !(body instanceof FormData)) {
                requestBody = JSON.stringify(body);
                headers['Content-Type'] = 'application/json';
            }

            const response = await fetch(apiUrl, {
                method: 'POST',
                body: requestBody,
                headers: headers
            });

            const json = await response.json();

            if (!response.ok) {
                throw new Error(json.message || 'An unknown API error occurred.');
            }

            return json;
        }

        async get(endpoint) {
            const apiUrl = `${this.settings.root}${this.settings.namespace}${endpoint}`;
            const headers = {
                'X-WP-Nonce': this.settings.nonce,
                'Accept': 'application/json'
            };

            const response = await fetch(apiUrl, {
                method: 'GET',
                headers: headers
            });

            const json = await response.json();

            if (!response.ok) {
                throw new Error(json.message || 'An unknown API error occurred.');
            }

            // В GET-запросах данные часто приходят напрямую, а не в поле 'data'
            return json.data || json;
        }

        async delete(endpoint) {
            const apiUrl = `${this.settings.root}${this.settings.namespace}${endpoint}`;
            const headers = {
                'X-WP-Nonce': this.settings.nonce,
            };

            const response = await fetch(apiUrl, {
                method: 'DELETE',
                headers: headers
            });

            const json = await response.json();

            if (!response.ok) {
                throw new Error(json.message || 'An unknown API error occurred.');
            }

            return json;
        }
    }

    /**
     * Управляет модальными окнами.
     */
    class ModalManager {
        constructor(apiClient) {
            this.apiClient = apiClient;
            this.modal = null;
            this.modalBody = null;
        }

        init() {
            this.modal = document.getElementById('usp-modal-container');
            this.modalBody = this.modal ? this.modal.querySelector('.usp-modal-body') : null;

            if (!this.modal || !this.modalBody) {
                console.error('ModalManager: Modal container or body not found.');
                return;
            }
            document.body.addEventListener('click', this.handleDocumentClick.bind(this));
        }

        async handleDocumentClick(e) {
            const trigger = e.target.closest('.usp-modal-trigger');
            if (trigger) {
                e.preventDefault();
                const formType = trigger.dataset.form;
                if (formType) {
                    this.open(formType);
                }
            }

            if (e.target.matches('.usp-modal-close') || e.target === this.modal) {
                e.preventDefault();
                this.close();
            }
        }

        async open(formType) {
            this.modalBody.innerHTML = '<p>Loading...</p>';
            this.modal.classList.add('is-visible');

            try {
                const json = await this.apiClient.post(`/modal-form/${formType}`);
                this.modalBody.innerHTML = json.html;
            } catch (error) {
                this.modalBody.innerHTML = `<p style="color: red;">${error.message}</p>`;
            }
        }

        close() {
            this.modal.classList.remove('is-visible');
            this.modal.addEventListener('transitionend', this.closeOnTransitionEnd.bind(this), { once: true });
        }

        closeOnTransitionEnd() {
            this.modalBody.innerHTML = '';
        }
    }

    /**
     * Управляет UI-компонентами.
     */
    class UIManager {
        constructor(apiClient, assetLoader) {
            this.apiClient = apiClient;
            this.assetLoader = assetLoader;
        }


        initTabs(containerSelector) {
            const container = document.querySelector(containerSelector);
            if (!container) return;

            const menu = container.querySelector('.usp-settings-tabs-menu');
            if (!menu) return;

            menu.addEventListener('click', (e) => {
                if (e.target.tagName !== 'A') return;
                e.preventDefault();

                menu.querySelectorAll('a').forEach(a => a.classList.remove('active'));
                container.querySelectorAll('.usp-tab-pane').forEach(pane => pane.classList.remove('active'));

                const targetLink = e.target;
                const targetPaneId = targetLink.getAttribute('href');
                const targetPane = container.querySelector(targetPaneId);

                targetLink.classList.add('active');
                if (targetPane) {
                    targetPane.classList.add('active');
                }
            });
        }

        /**
         * Инициализирует вкладки для страницы аккаунта.
         * @param {string} containerSelector - Селектор для основного контейнера (.usp-account-wrapper).
         */
        initAccountTabs(containerSelector) {
            const accountWrapper = document.querySelector(containerSelector);
            if (!accountWrapper) {
                return;
            }

            // Активируем контент для начальной активной вкладки при загрузке страницы
            const initialActiveLink = accountWrapper.querySelector('.usp-account-menu a.active');
            if (initialActiveLink) {
                const initialTargetPaneId = initialActiveLink.getAttribute('href');
                if (initialTargetPaneId && initialTargetPaneId !== '#') {
                    const initialTargetPane = accountWrapper.querySelector(initialTargetPaneId);
                    if (initialTargetPane) {
                        initialTargetPane.classList.add('is-loaded'); // Помечаем, что контент уже есть
                        initialTargetPane.classList.add('active');
                    }
                }
            }

            accountWrapper.addEventListener('click', (e) => {
                const link = e.target.closest('a');
                if (!link) {
                    return;
                }

                const targetPaneId = link.getAttribute('href');

                // Если кликнули по родительскому пункту с подменю,
                // автоматически переходим на первую дочернюю вкладку.
                const parentMenuItem = link.closest('.has-submenu');
                if (parentMenuItem && !link.closest('.usp-account-submenu') && targetPaneId === '#') {
                    const firstSubmenuLink = parentMenuItem.querySelector('.usp-account-submenu a');
                    if (firstSubmenuLink) {
                        e.preventDefault(); // Предотвращаем стандартное поведение для родительской ссылки с '#'
                        firstSubmenuLink.click();
                        return;
                    }
                }

                // Только если ссылка является частью меню вкладок и ее href - это локальный якорь (#tab-id)
                const isTabNavigationLink = link.closest('.usp-account-menu');
                if (!isTabNavigationLink || !targetPaneId || !targetPaneId.startsWith('#') || targetPaneId === '#') {
                    return; // Игнорируем ссылки, не относящиеся к навигации вкладок
                }

                e.preventDefault(); // Предотвращаем стандартное поведение только для ссылок вкладок

                const targetPane = accountWrapper.querySelector(targetPaneId);
                if (!targetPane) {
                    console.error(`Usp Account: No tab pane found with id ${targetPaneId}`);
                    return;
                }

                // Находим меню, в котором произошел клик
                const currentMenu = link.closest('.usp-account-menu');
                if (!currentMenu) {
                    return;
                }

                // Убираем active со всех ссылок и панелей
                accountWrapper.querySelectorAll('.usp-account-menu a.active').forEach(a => a.classList.remove('active'));
                accountWrapper.querySelectorAll('.usp-account-menu-item.is-active').forEach(item => item.classList.remove('is-active'));
                accountWrapper.querySelectorAll('.usp-account-tab-pane.active').forEach(pane => pane.classList.remove('active'));

                // Добавляем active к нажатой ссылке и ее родителям
                link.classList.add('active');
                link.closest('.usp-account-menu-item')?.classList.add('is-active');

                // Если это под-вкладка, также делаем активной родительскую ссылку
                const parentLink = link.closest('.usp-account-submenu')?.closest('.usp-account-menu-item')?.querySelector('a');
                if (parentLink) {
                    parentLink.classList.add('active');
                }

                // Показываем нужный контент
                targetPane.classList.add('active');

                // Если контент нужно загрузить по REST
                if (targetPane.dataset.contentType === 'rest' && !targetPane.classList.contains('is-loaded')) {
                    this._loadRestContent(targetPane);
                }

                // Обновляем URL в адресной строке
                const tabParamName = window.uspCoreParams?.tabParam || 'tab';
                const url = new URL(window.location);
                const tabId = targetPaneId.substring(1); // Убираем #

                if (url.searchParams.get(tabParamName) !== tabId) {
                    url.searchParams.set(tabParamName, tabId);
                    // Обновляем историю браузера
                    window.history.pushState({ path: url.toString() }, '', url.toString());
                }
            });
        }

        showFrontendNotice(message, type, targetElement, position = 'beforebegin') {
            if (!targetElement) {
                console.error('UIManager.showFrontendNotice: targetElement is not defined.');
                return;
            }

            const oldNotices = document.querySelectorAll('.usp-notice');
            oldNotices.forEach(notice => notice.remove());

            const noticeHtml = `
                <div class="usp-notice usp-notice-${type} is-dismissible">
                    ${message.startsWith('<ul>') ? message : `<p>${message}</p>`}
                </div>`;
            targetElement.insertAdjacentHTML(position, noticeHtml);
        }

        /**
         * Показывает уведомление в админ-панели.
         * @param {string} message - Текст уведомления.
         * @param {'success'|'error'|'warning'|'info'} type - Тип уведомления.
         * @param {string} containerSelector - Селектор контейнера для уведомления.
         */
        showAdminNotice(message, type, containerSelector) {
            const container = document.querySelector(containerSelector);
            if (container) container.innerHTML = `<div class="notice notice-${type} is-dismissible"><p>${message}</p></div>`;
        }

        /**
         * Показывает стандартное уведомление WordPress вверху страницы.
         * @param {string} message - Текст уведомления.
         * @param {'success'|'error'|'warning'|'info'} type - Тип уведомления.
         * @param {boolean} isDismissible - Можно ли закрыть уведомление.
         */
        showNotice(message, type = 'info', isDismissible = true) {
            // Удаляем предыдущие динамические уведомления, чтобы они не накапливались
            const oldNotices = document.querySelectorAll('.usp-dynamic-notice');
            oldNotices.forEach(notice => notice.remove());

            const notice = document.createElement('div');
            // Добавляем кастомный класс для легкого поиска и удаления в будущем
            notice.className = `notice notice-${type} usp-dynamic-notice`;
            if (isDismissible) {
                notice.classList.add('is-dismissible');
            }
            notice.innerHTML = `<p>${message}</p>`;

            // Находим основной заголовок h1 в админке
            const mainHeader = document.querySelector('.wrap h1');
            if (mainHeader) {
                mainHeader.after(notice);
            } else {
                // Если не нашли, вставляем в начало .wrap
                const wrap = document.querySelector('.wrap');
                if (wrap) {
                    wrap.prepend(notice);
                }
            }
        }

        async _loadRestContent(pane) {
            const url = pane.dataset.contentSource;
            const l10n = window.uspL10n || { loading: 'Loading...', loadError: 'Failed to load content.' };

            pane.innerHTML = `<p>${l10n.loading}</p>`;
            pane.classList.add('is-loading');

            try {
                const data = await this.apiClient.get(url);

                // 1. Вставляем HTML
                pane.innerHTML = data.html;

                // 2. Загружаем ассеты, если они есть
                if (data.assets) {
                    await this.assetLoader.load(
                        data.assets.scripts,
                        data.assets.styles,
                        data.assets.localized
                    );
                }

                pane.classList.add('is-loaded');

                // 3. (Опционально) Отправляем событие, чтобы другие скрипты могли инициализироваться
                document.dispatchEvent(new CustomEvent('usp:tabContentLoaded', { detail: { pane } }));

            } catch (error) {
                const errorMessage = error.message || l10n.loadError;
                pane.innerHTML = `<p class="usp-error">${errorMessage}</p>`;
                console.error('REST content load failed:', error);
            } finally {
                pane.classList.remove('is-loading');
            }
        }
    }

    /**
     * Динамически загружает скрипты и стили на страницу.
     */
    class AssetLoader {
        constructor() {
            this.loadedScripts = new Set();
            this.loadedStyles = new Set();
        }

        /**
         * Загружает массив ассетов.
         * @param {string[]} scriptUrls - Массив URL скриптов.
         * @param {string[]} styleUrls - Массив URL стилей.
         * @param {object[]} localizedData - Массив с данными для локализации.
         * @returns {Promise<void>}
         */
        async load(scriptUrls = [], styleUrls = [], localizedData = []) {
            const stylePromises = styleUrls.map(url => this.loadStyle(url));
            await Promise.all(stylePromises);

            // Обрабатываем локализацию до загрузки скриптов
            localizedData.forEach(item => {
                // Глубокое слияние объектов, чтобы не перезаписать существующие ключи
                window[item.objectName] = window[item.objectName] || {};
                Object.assign(window[item.objectName], item.data);
            });

            // Скрипты грузим последовательно, чтобы сохранить порядок зависимостей
            for (const url of scriptUrls) {
                await this.loadScript(url);
            }
        }

        /**
         * Загружает один скрипт.
         * @param {string} url
         * @returns {Promise<void>}
         */
        loadScript(url) {
            if (this.loadedScripts.has(url)) {
                return Promise.resolve();
            }

            return new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = url;
                script.async = false; // Гарантирует последовательное выполнение
                script.onload = () => {
                    this.loadedScripts.add(url);
                    resolve();
                };
                script.onerror = () => {
                    console.error(`Failed to load script: ${url}`);
                    reject(new Error(`Failed to load script: ${url}`));
                };
                document.body.appendChild(script);
            });
        }

        /**
         * Загружает один файл стилей.
         * @param {string} url
         * @returns {Promise<void>}
         */
        loadStyle(url) {
            if (this.loadedStyles.has(url)) {
                return Promise.resolve();
            }

            return new Promise((resolve, reject) => {
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = url;
                link.onload = () => {
                    this.loadedStyles.add(url);
                    resolve();
                };
                link.onerror = () => {
                    console.error(`Failed to load style: ${url}`);
                    reject(new Error(`Failed to load style: ${url}`));
                };
                document.head.appendChild(link);
            });
        }
    }

    /**
     * Управляет загрузкой аватара пользователя.
     */
    class AvatarUploader {
        constructor(apiClient) {
            this.apiClient = apiClient;
            this.l10n = window.uspAccountTheme?.l10n || {
                uploading: 'Uploading...',
                error: 'Error',
                success: 'Success!',
                networkError: 'A network error occurred. Please try again.'
            };
        }

        init(containerSelector = '#usp-avatar-block') {
            const avatarBlock = document.querySelector(containerSelector);
            if (!avatarBlock) return;

            const uploaderEl = avatarBlock.querySelector('.usp-account-avatar-uploader');
            if (!uploaderEl) return;

            const fileInput = uploaderEl.querySelector('.usp-avatar-input');
            const statusEl = uploaderEl.querySelector('.usp-avatar-status');
            const avatarImg = avatarBlock.querySelector('.usp-account-avatar-img');

            const showStatus = (message, isError = false) => {
                statusEl.textContent = message;
                statusEl.style.display = 'block';
                statusEl.style.color = isError ? '#ff595e' : '#fff';
                setTimeout(() => {
                    statusEl.style.display = 'none';
                }, 3000);
            };

            uploaderEl.addEventListener('click', () => fileInput.click());

            fileInput.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (!file) return;

                const { config, signature } = uploaderEl.dataset;

                const uploader = new FileUploader(file, {
                    apiClient: this.apiClient,
                    config,
                    signature,
                    validationRules: JSON.parse(config || '{}'),
                    onProgress: (message) => showStatus(message),
                    onSuccess: async (uploadResult) => {
                        showStatus(this.l10n.success);
                        avatarImg.src = uploadResult.previewUrl;

                        try {
                            await this.apiClient.post('/user/avatar', {
                                attachmentId: uploadResult.attachmentId
                            });
                        } catch (saveError) {
                            console.error('Failed to save avatar:', saveError);
                            showStatus(`${this.l10n.error}: ${saveError.message}`, true);
                        }
                    },
                    onError: (error) => {
                        showStatus(`${this.l10n.error}: ${error.message}`, true);
                    },
                    onFinally: () => {
                        fileInput.value = '';
                    }
                });

                uploader.process();
            });
        }
    }

    /**
     * @namespace uspL10n
     * @property {object} uploader
     * @property {string} uploader.fileTooLarge - "File is too large. Maximum size is {maxSize} MB."
     * @property {string} uploader.invalidFileType - "Invalid file type."
     * @property {string} uploader.imageTooSmall - "Image is too small. Minimum dimensions are {minWidth}x{minHeight}px."
     * @property {string} uploader.imageTooLarge - "Image is too large. Maximum dimensions are {maxWidth}x{maxHeight}px."
     * @property {string} uploader.imageReadError - "Could not read image dimensions."
     * @property {string} uploader.uploading - "Uploading..."
     * @property {string} uploader.success - "Success!"
     * @property {string} uploader.error - "Error: {message}"
     * @property {string} loading - "Loading..."
     * @property {string} loadError - "Failed to load content."
     */

        // Убедимся, что l10n объект существует
    const l10n = window.uspL10n || { uploader: {} };

    // --- FileUploader остается в основном без изменений, так как он уже является классом ---

    /**
     * Handles file validation and uploading.
     */
    class FileUploader {
        /**
         * @param {File} file The file to upload.
         * @param {object} options
         * @param {object} options.validationRules Rules from data attributes.
         * @param {string} options.config The JSON string with field config.
         * @param {string} options.signature The signature for the config.
         * @param {function(string): void} [options.onProgress] Callback for progress updates.
         * @param {function(object): void} [options.onSuccess] Callback for successful upload.
         * @param {function(Error): void} [options.onError] Callback for upload errors.
         * @param {function(): void} [options.onFinally] Callback that runs after success or error.
         */
        constructor(file, options) {
            this.apiClient = options.apiClient;
            this.file = file;
            this.options = {
                validationRules: {},
                config: '',
                signature: '',
                onProgress: () => {},
                onSuccess: () => {},
                onError: () => {},
                onFinally: () => {},
                ...options
            };

            if (!this.apiClient) {
                throw new Error('FileUploader requires an apiClient instance.');
            }
        }

        /**
         * Validates and uploads the file.
         */
        async process() {
            try {
                this.options.onProgress(l10n.uploader?.validating || 'Validating...');
                const validationErrors = await this.validate();
                if (validationErrors.length > 0) {
                    throw new Error(validationErrors.join('\n'));
                }

                this.options.onProgress(l10n.uploader?.uploading || 'Uploading...');
                const result = await this.upload();
                this.options.onSuccess(result);
                this.options.onProgress(l10n.uploader?.success || 'Success!');

            } catch (error) {
                this.options.onError(error);
                const errorMessage = (l10n.uploader?.error || 'Error: {message}').replace('{message}', error.message);
                this.options.onProgress(errorMessage);
            } finally {
                this.options.onFinally();
            }
        }

        /**
         * Validates the file against the rules.
         * @returns {Promise<string[]>} A promise that resolves with an array of error messages.
         */
        async validate() {
            const errors = [];
            const { allowedTypes, maxSize, minWidth, minHeight, maxWidth, maxHeight } = this.options.validationRules;

            if (maxSize && (this.file.size / 1024 / 1024) > parseFloat(maxSize)) {
                errors.push((l10n.uploader?.fileTooLarge || 'File is too large. Maximum size is {maxSize} MB.').replace('{maxSize}', maxSize));
            }

            if (allowedTypes) {
                const allowed = allowedTypes.split(',').map(t => t.trim());
                if (!allowed.includes(this.file.type)) {
                    errors.push(l10n.uploader?.invalidFileType || 'Invalid file type.');
                }
            }

            if (this.file.type.startsWith('image/')) {
                await new Promise((resolve) => {
                    const img = new Image();
                    img.onload = () => {
                        if ((minWidth && img.width < minWidth) || (minHeight && img.height < minHeight)) {
                            errors.push((l10n.uploader?.imageTooSmall || 'Image is too small. Minimum dimensions are {minWidth}x{minHeight}px.').replace('{minWidth}', minWidth).replace('{minHeight}', minHeight));
                        }
                        if ((maxWidth && img.width > maxWidth) || (maxHeight && img.height > maxHeight)) {
                            errors.push((l10n.uploader?.imageTooLarge || 'Image is too large. Maximum dimensions are {maxWidth}x{maxHeight}px.').replace('{maxWidth}', maxWidth).replace('{maxHeight}', maxHeight));
                        }
                        resolve();
                    };
                    img.onerror = () => {
                        errors.push(l10n.uploader?.imageReadError || 'Could not read image dimensions.');
                        resolve();
                    };
                    img.src = URL.createObjectURL(this.file);
                });
            }

            return errors;
        }

        /**
         * Uploads the file to the server.
         * @returns {Promise<object>} A promise that resolves with the server's JSON response.
         */
        upload() {
            const formData = new FormData();
            formData.append('file', this.file);
            formData.append('config', this.options.config);
            formData.append('signature', this.options.signature);

            return this.apiClient.post('/files/upload', formData);
        }
    }

    /**
     * Универсальный клиент для работы с Server-Sent Events.
     */
    class SSEClient {
        /**
         * @param {string} endpointUrl Полный URL для подключения к SSE.
         */
        constructor(endpointUrl) {
            if (!endpointUrl) {
                throw new Error('SSEClient: endpointUrl is required.');
            }
            this.endpointUrl = endpointUrl;
            this.eventSource = null;
            this.listeners = {};
        }

        /**
         * Устанавливает соединение с сервером.
         */
        connect() {
            if (this.eventSource) {
                console.warn('SSEClient: Already connected or connecting.');
                return;
            }

            try {
                this.eventSource = new EventSource(this.endpointUrl);

                this.eventSource.onopen = () => {
                    console.log('SSE connection established.');
                };

                this.eventSource.onerror = (error) => {
                    console.error('SSE connection error. The browser will attempt to reconnect.', error);
                    // Если сервер ответил, например, 401, браузер может прекратить попытки.
                    // Закрываем соединение, чтобы можно было создать новое.
                    if (this.eventSource.readyState === EventSource.CLOSED) {
                        this.eventSource = null;
                    }
                };

                // Применяем все ранее зарегистрированные обработчики
                Object.keys(this.listeners).forEach(eventName => {
                    this.listeners[eventName].forEach(callback => {
                        this.eventSource.addEventListener(eventName, callback);
                    });
                });

            } catch (e) {
                console.error('Failed to create EventSource:', e);
            }
        }

        /**
         * Добавляет обработчик для кастомного события.
         * @param {string} eventName Имя события.
         * @param {function(object): void} callback Функция, которая будет вызвана с распарсенными данными.
         * @returns {this}
         */
        on(eventName, callback) {
            const handler = (event) => {
                try {
                    const data = JSON.parse(event.data);
                    callback(data);
                } catch (e) {
                    console.error(`Failed to parse JSON for SSE event "${eventName}":`, event.data, e);
                }
            };

            if (!this.listeners[eventName]) {
                this.listeners[eventName] = [];
            }
            this.listeners[eventName].push(handler);

            // Если соединение уже установлено, добавляем обработчик "на лету"
            if (this.eventSource) {
                this.eventSource.addEventListener(eventName, handler);
            }

            return this;
        }

        /**
         * Закрывает соединение.
         */
        close() {
            if (this.eventSource) {
                this.eventSource.close();
                this.eventSource = null;
                console.log('SSE connection closed by client.');
            }
        }
    }

    /**
     * Главный класс, инициализирующий все компоненты.
     */
    class UserSpaceCore {
        constructor() {
            try {
                this.api = new ApiClient(window.uspApiSettings);
                this.assetLoader = new AssetLoader();
                this.ui = new UIManager(this.api, this.assetLoader);
                this.modalManager = new ModalManager(this.api);
                this.avatarUploader = new AvatarUploader(this.api);
                this.FileUploader = (file, options) => new FileUploader(file, { ...options, apiClient: this.api });
            } catch (error) {
                console.error('Failed to initialize UserSpaceCore:', error.message);
                // Создаем "пустышки", чтобы избежать ошибок при вызове методов
                this.api = {
                    post: () => Promise.reject('API client not initialized'),
                    get: () => Promise.reject('API client not initialized'),
                    delete: () => Promise.reject('API client not initialized'),
                    getEndpointUrl: () => ''
                };
                this.assetLoader = { load: () => Promise.resolve() };
                this.ui = { showAdminNotice: () => {}, showFrontendNotice: () => {}, initTabs: () => {}, initAccountTabs: () => {} };
                this.modalManager = { init: () => {} };
                this.avatarUploader = { init: () => {} };
                this.FileUploader = () => { throw new Error('FileUploader not initialized'); };
                return;
            }

            this.init();
        }

        /**
         * Создает и возвращает новый экземпляр SSE-клиента.
         * @param {string} endpointUrl - Полный URL для подключения.
         * @returns {SSEClient}
         */
        createSseClient(endpointUrl) {
            return new SSEClient(endpointUrl);
        }

        /**
         * Проверяет, авторизован ли текущий пользователь.
         * @returns {boolean}
         */
        isUserLoggedIn() {
            return document.body.classList.contains('logged-in') || document.body.classList.contains('wp-admin');
        }

        init() {
            this.modalManager.init();
            this.avatarUploader.init();
            this.ui.initAccountTabs('.usp-account-wrapper');
        }
    }

    // Создаем и прикрепляем единственный глобальный объект после загрузки DOM
    document.addEventListener('DOMContentLoaded', () => {
        window.UspCore = new UserSpaceCore();
        // window.UspCore.avatarUploader.init(); // Можно вызвать и так, если не нужен автозапуск
    });

})(window, document);