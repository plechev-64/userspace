<?php

namespace UserSpace\Core;

use InvalidArgumentException;

/**
 * Описывает интерфейс для сервиса, управляющего рендерингом шаблонов.
 */
interface TemplateManagerInterface
{
    /**
     * Рендерит шаблон и возвращает его содержимое в виде строки.
     *
     * @param string $key Ключ шаблона.
     * @param array<string, mixed> $variables Переменные для передачи в шаблон.
     * @return string
     */
    public function render(string $key, array $variables = []): string;

    /**
     * Возвращает путь к файлу шаблона по его ключу.
     *
     * @param string $key Ключ шаблона.
     * @return string
     * @throws InvalidArgumentException Если шаблон с таким ключом не зарегистрирован.
     */
    public function getTemplatePath(string $key): string;
}