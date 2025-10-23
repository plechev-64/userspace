<?php

namespace UserSpace\Common\Service;

use InvalidArgumentException;
use UserSpace\Core\Container\Params;
use UserSpace\Core\String\StringFilterInterface;
use UserSpace\Core\TemplateManagerInterface;

class TemplateManager implements TemplateManagerInterface
{
    /**
     * @param Params $templates
     * @param StringFilterInterface $str
     */
    public function __construct(
        private readonly Params                $templates,
        private readonly StringFilterInterface $str
    )
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
                return sprintf('<!-- Template "%s" not found or not readable at path: %s -->', $this->str->escHtml($key), $this->str->escHtml($templatePath));
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
        if ($this->templates->get($key) === null) {
            throw new InvalidArgumentException(sprintf($this->str->translate('Template with key "%s" is not registered.'), $key));
        }

        return $this->templates->get($key);
    }
}