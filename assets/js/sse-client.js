/**
 * Универсальный клиент для работы с Server-Sent Events.
 */
class SSEClient {
    /**
     * @param {string} endpointUrl Полный URL для подключения к SSE.
     */
    constructor(endpointUrl) {
        this.endpointUrl = endpointUrl;
        this.eventSource = null;
    }

    /**
     * Устанавливает соединение с сервером.
     */
    connect() {
        if (this.eventSource && this.eventSource.readyState !== EventSource.CLOSED) {
            return;
        }

        this.eventSource = new EventSource(this.endpointUrl);

        this.eventSource.onerror = () => {
            // Браузер автоматически пытается переподключиться.
            // Можно добавить кастомную логику, например, показать индикатор "соединение потеряно".
            console.log('SSE connection error or closed. Reconnecting...');
        };
    }

    /**
     * Добавляет обработчик для кастомного события.
     * @param {string} eventName Имя события.
     * @param {function(object): void} callback Функция, которая будет вызвана с распарсенными данными.
     */
    on(eventName, callback) {
        if (!this.eventSource) {
            this.connect();
        }

        this.eventSource.addEventListener(eventName, (event) => {
            const data = JSON.parse(event.data);
            callback(data);
        });
    }
}