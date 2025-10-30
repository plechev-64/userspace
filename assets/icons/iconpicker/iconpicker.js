/**
 * UserSpace Icon Picker - Vanilla JS
 * A lightweight, dependency-free icon picker.
 */

document.addEventListener('DOMContentLoaded', () => {
    // Конфигурация. В идеале, этот объект должен приходить с сервера,
    // например, через wp_localize_script.
    const uspIconPickerConfig = {
        // Список иконок. Я взял его из вашего старого файла.
        icons: [
            { title: "fa-user-cog", searchTerms: ["settings", "gear"] },
            { title: "fa-users-cog", searchTerms: ["settings", "gear"] },
            { title: "fa-cog", searchTerms: ["settings", "gear"] },
            { title: "fa-user-secret", searchTerms: ["whisper", "spy", "incognito", "privacy"] },
            { title: "fa-user", searchTerms: ["person", "man", "head", "profile", "account"] },
            { title: "fa-users", searchTerms: ["people", "profiles", "persons"] },
            { title: "fa-address-book", searchTerms: ["bookmark"] },
            { title: "fa-exclamation-triangle", searchTerms: ["warning", "error", "problem", "notification", "alert", "danger"] },
            { title: "fa-info-circle", searchTerms: ["help", "information", "more", "details"] },
            { title: "fa-bell", searchTerms: ["alert", "reminder", "notification"] },
            { title: "fa-comment", searchTerms: ["speech", "notification", "note", "chat", "feedback", "message"] },
            { title: "fa-envelope", searchTerms: ["email", "e-mail", "letter", "support", "mail", "message"] },
            { title: "fa-paper-plane", searchTerms: ["social", "send"] },
            { title: "fa-sync", searchTerms: ["spinner", "load", "loading", "progress"] },
            { title: "fa-list", searchTerms: ["ul", "ol", "checklist", "todo"] },
            { title: "fa-link", searchTerms: ["chain"] },
            { title: "fa-save", searchTerms: ["floppy"] },
            { title: "fa-copy", searchTerms: ["duplicate", "clone", "file"] },
            { title: "fa-file", searchTerms: ["new", "page", "pdf", "document"] },
            { title: "fa-image", searchTerms: ["photo", "album", "picture"] },
            { title: "fa-camera", searchTerms: ["photo", "picture", "record"] },
            { title: "fa-code", searchTerms: ["html", "brackets"] },
            { title: "fa-pencil", searchTerms: ["write", "edit", "update"] },
            { title: "fa-lock", searchTerms: ["protect", "admin", "security"] },
            { title: "fa-unlock", searchTerms: ["protect", "admin", "password"] },
            { title: "fa-folder", searchTerms: ["directory"] },
            { title: "fa-folder-open", searchTerms: ["directory"] },
            { title: "fa-check", searchTerms: ["checkmark", "done", "todo", "agree", "accept", "confirm", "ok"] },
            { title: "fa-times", searchTerms: ["close", "exit", "x", "delete", "remove"] },
            { title: "fa-plus", searchTerms: ["add", "new", "create", "expand"] },
            { title: "fa-minus", searchTerms: ["hide", "minify", "delete", "remove", "collapse"] },
            { title: "fa-trash", searchTerms: ["garbage", "delete", "remove"] },
            { title: "fa-shopping-cart", searchTerms: ["checkout", "buy", "purchase", "payment"] },
            { title: "fa-dollar-sign", searchTerms: ["usd", "price", "money", "pay", "cash"] },
            { title: "fa-angle-down", searchTerms: ["arrow"] },
            { title: "fa-angle-up", searchTerms: ["arrow"] },
            { title: "fa-angle-left", searchTerms: ["previous", "back", "arrow"] },
            { title: "fa-angle-right", searchTerms: ["next", "forward", "arrow"] },
            { title: "fa-sign-in", searchTerms: ["enter", "join", "login", "signin"] },
            { title: "fa-sign-out", searchTerms: ["logout", "leave", "exit"] },
            { title: "fa-upload", searchTerms: ["import"] },
            { title: "fa-download", searchTerms: ["export"] },
            { title: "fa-home", searchTerms: ["main", "house"] },
            { title: "fa-eye", searchTerms: ["show", "visible", "views"] },
            { title: "fa-eye-slash", searchTerms: ["toggle", "hide", "invisible"] },
            { title: "fa-search", searchTerms: ["magnify", "zoom", "enlarge"] },
            { title: "fa-key", searchTerms: ["unlock", "password"] },
            { title: "fa-magic", searchTerms: ["wizard", "automatic", "autocomplete"] },
            { title: "fa-clock", searchTerms: ["watch", "timer", "late", "timestamp", "date"] },
            // ... добавьте сюда остальные иконки при необходимости
        ],
        searchPlaceholder: 'Search icons...' // Текст для поля поиска
    };

    // Переменная для хранения активного пикера
    let activePicker = null;

    /**
     * Класс для управления одним экземпляром Icon Picker.
     */
    class IconPicker {
        constructor(inputElement) {
            this.input = inputElement;
            this.popover = null;
            this.iconItems = [];
            this.preview = this.createPreviewElement(); // Исправляем вызов на правильный метод

            this.createPopover();
            this.wrapInputAndPreview(); // Оборачиваем поле и превью в контейнер
            this.input.addEventListener('focus', () => this.show());
            this.updatePreview(); // Обновляем превью при инициализации, если есть значение
        }

        // Находит элемент для предпросмотра иконки
        createPreviewElement() {
            const newPreview = document.createElement('span');
            newPreview.className = 'usp-icon-preview';
            return newPreview;
        }

        // Оборачивает поле ввода и превью в новый контейнер
        wrapInputAndPreview() {
            this.wrapper = document.createElement('div');
            this.wrapper.className = 'usp-icon-picker-wrapper';

            // Вставляем обертку перед полем ввода
            this.input.parentNode.insertBefore(this.wrapper, this.input);

            // Перемещаем превью и поле ввода внутрь обертки
            this.wrapper.appendChild(this.preview);
            this.wrapper.appendChild(this.input);
        }

        // Создает DOM всплывающего окна
        createPopover() {
            this.popover = document.createElement('div');
            this.popover.className = 'usp-iconpicker-popover';

            const searchInput = document.createElement('input');
            searchInput.type = 'search';
            searchInput.className = 'usp-iconpicker-search';
            searchInput.placeholder = uspIconPickerConfig.searchPlaceholder;
            searchInput.autocomplete = 'off';
            searchInput.addEventListener('keyup', (e) => this.filter(e.target.value));

            const itemsContainer = document.createElement('div');
            itemsContainer.className = 'usp-iconpicker-items';

            uspIconPickerConfig.icons.forEach(iconData => {
                const item = document.createElement('span');
                item.className = 'usp-iconpicker-item';
                item.title = iconData.title;
                item.dataset.icon = iconData.title;
                item.dataset.search = (iconData.searchTerms || []).join(' ');

                const i = document.createElement('i');
                i.className = `uspi ${iconData.title}`;
                item.appendChild(i);

                item.addEventListener('click', () => this.select(iconData.title));

                itemsContainer.appendChild(item);
                this.iconItems.push(item);
            });

            this.popover.appendChild(searchInput);
            this.popover.appendChild(itemsContainer);
            document.body.appendChild(this.popover);
        }

        // Показывает всплывающее окно
        show() {
            if (activePicker && activePicker !== this) {
                activePicker.hide();
            }
            activePicker = this;

            const inputRect = this.input.getBoundingClientRect();
            this.popover.style.display = 'block';
            this.popover.style.top = `${inputRect.bottom + window.scrollY}px`;
            this.popover.style.left = `${inputRect.left + window.scrollX}px`;
            this.popover.classList.add('is-visible');
            this.updateSelected();
        }

        // Скрывает всплывающее окно
        hide() {
            this.popover.classList.remove('is-visible');
            this.popover.style.display = 'none';
            if (activePicker === this) {
                activePicker = null;
            }
        }

        // Выбирает иконку
        select(iconClass) {
            this.input.value = iconClass;
            // Инициируем событие 'input', чтобы другие скрипты (например, React/Vue) могли отреагировать
            this.input.dispatchEvent(new Event('input', { bubbles: true }));
            this.updatePreview();
            this.hide();
        }

        // Обновляет иконку предпросмотра
        updatePreview() {
            if (this.preview) {
                this.preview.innerHTML = this.input.value ? `<i class="uspi ${this.input.value}"></i>` : '';
            }
        }

        // Обновляет выделенную иконку в списке
        updateSelected() {
            const currentValue = this.input.value;
            this.iconItems.forEach(item => {
                item.classList.toggle('is-selected', item.dataset.icon === currentValue);
            });
        }

        // Фильтрует иконки по поисковому запросу
        filter(query) {
            const searchTerm = query.toLowerCase().trim();
            this.iconItems.forEach(item => {
                const title = item.dataset.icon.toLowerCase();
                const searchData = item.dataset.search.toLowerCase();
                const isVisible = title.includes(searchTerm) || searchData.includes(searchTerm);
                item.style.display = isVisible ? '' : 'none';
            });
        }
    }

    // Инициализация для всех полей с классом .usp-icon-picker
    function initIconPickers() {
        document.querySelectorAll('.usp-icon-picker').forEach(input => {
            if (!input.dataset.uspPickerInitialized) {
                input.dataset.uspPickerInitialized = 'true';
                new IconPicker(input);
            }
        });
    }

    // Глобальный обработчик кликов для закрытия пикера
    document.addEventListener('click', (e) => {
        if (activePicker && !activePicker.popover.contains(e.target) && e.target !== activePicker.input) {
            activePicker.hide();
        }
    });

    // Запускаем инициализацию
    initIconPickers();

    // Если у вас есть динамически добавляемые поля (например, в конструкторе форм),
    // вы можете повторно вызывать `initIconPickers()` или использовать MutationObserver.

    /**
     * Наблюдатель за изменениями в DOM для автоматической инициализации
     * новых полей выбора иконок, добавленных через AJAX.
     */
    const observer = new MutationObserver((mutationsList) => {
        let needsReInit = false;
        for (const mutation of mutationsList) {
            if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                for (const node of mutation.addedNodes) {
                    // Проверяем, является ли сам добавленный узел полем или содержит ли он такие поля
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        if (node.matches('.usp-icon-picker') || node.querySelector('.usp-icon-picker')) {
                            needsReInit = true;
                            break; // Достаточно одного совпадения
                        }
                    }
                }
            }
            if (needsReInit) {
                break; // Выходим из внешнего цикла
            }
        }
        if (needsReInit) {
            initIconPickers();
        }
    });

    // Начинаем наблюдение за всем документом
    observer.observe(document.body, {
        childList: true, // Отслеживать добавление/удаление дочерних узлов
        subtree: true    // Отслеживать изменения во всех потомках
    });
});