<?php

namespace UserSpace\Common\Service;

use InvalidArgumentException;

class TemplateManager
{
    /**
     * @param array<string, string> $templates
     */
    public function __construct(private readonly array $templates)
    {
    }

    /**
     * Рендерит шаблон и возвращает его содержимое в виде строки.
     *
     * @param string $key Ключ шаблона.
     * @param array<string, mixed> $variables Переменные для передачи в шаблон.
     * @return string
     */
    public function render(string $key, array $variables = []): string
    {
        $templatePath = $this->getTemplatePath($key);

        if (!is_readable($templatePath)) {
            // В режиме разработки можно выводить более явную ошибку
            if (defined('WP_DEBUG') && WP_DEBUG) {
                return sprintf('<!-- Template "%s" not found or not readable at path: %s -->', esc_html($key), esc_html($templatePath));
            }
            return '';
        }

        ob_start();
        // Извлекаем переменные в локальную область видимости
        extract($variables, EXTR_SKIP);
        include $templatePath;
        return ob_get_clean();
    }

    /**
     * Возвращает путь к файлу шаблона по его ключу.
     *
     * @param string $key Ключ шаблона.
     * @return string
     * @throws InvalidArgumentException Если шаблон с таким ключом не зарегистрирован.
     */
    public function getTemplatePath(string $key): string
    {
        if (!isset($this->templates[$key])) {
            throw new InvalidArgumentException(sprintf('Template with key "%s" is not registered.', $key));
        }

        return $this->templates[$key];
    }
}