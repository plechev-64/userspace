<?php

namespace UserSpace\Core;

use UserSpace\Common\Module\Settings\Src\Domain\OptionManagerInterface;

class SecurityHelper implements SecurityHelperInterface
{
    private const SECURITY_KEY_OPTION = 'usp_security_key';

    public function __construct(private readonly OptionManagerInterface $optionManager)
    {
    }

    /**
     * Генерирует подпись для массива данных.
     *
     * @param array $data Массив данных для подписи.
     * @return string Сгенерированная подпись.
     */
    public function sign(array $data): string
    {
        $preparedData = $this->prepareDataForSigning($data);
        $payload = json_encode($preparedData);
        $key = $this->getSecurityKey();

        return hash_hmac('sha256', $payload, $key);
    }

    /**
     * Проверяет подпись массива данных.
     *
     * @param array $data Массив данных, для которых проверяется подпись.
     * @param string $signature Подпись для проверки.
     * @return bool True, если подпись валидна, false в противном случае.
     */
    public function validate(array $data, string $signature): bool
    {
        $preparedData = $this->prepareDataForSigning($data);
        $expectedSignature = $this->sign($preparedData); // Sign the prepared data

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Возвращает секретный ключ безопасности.
     * @return string
     */
    public function getSecurityKey(): string
    {
        $key = $this->optionManager->get(self::SECURITY_KEY_OPTION);
        if (empty($key)) {
            $key = wp_generate_password(64, true, true);
            $this->optionManager->update(self::SECURITY_KEY_OPTION, $key);
        }
        return $key;
    }

    /**
     * Подготавливает массив данных для подписи, удаляя null-значения.
     * Это обеспечивает консистентность между подписываемыми и проверяемыми данными.
     *
     * @param array $data Исходный массив данных.
     * @return array Отфильтрованный массив данных.
     */
    private function prepareDataForSigning(array $data): array
    {
        // Используем ARRAY_FILTER_USE_BOTH для фильтрации по значению и ключу,
        // но здесь достаточно фильтрации по значению.
        // Важно: `false` значения не должны отбрасываться, если они значимы.
        // Например, `multiple: false` должно остаться.
        return array_filter($data, fn($value) => $value !== null);
    }
}