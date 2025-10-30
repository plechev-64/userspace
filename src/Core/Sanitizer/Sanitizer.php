<?php

namespace UserSpace\Core\Sanitizer;

use UserSpace\Core\String\StringFilterInterface;

class Sanitizer implements SanitizerInterface
{
    public function __construct(
        private readonly StringFilterInterface $stringFilter
    )
    {
    }

    public function sanitize(array $data, array $config): ClearedDataInterface
    {
        $sanitizedData = [];

        foreach ($data as $key => $value) {
            // Если значение null, не применяем санитизацию и сохраняем его как есть.
            if ($value === null) {
                $sanitizedData[$key] = null;
                continue;
            }

            $rule = $config[$key] ?? null;

            if (isset($rule)) {
                $sanitizedData[$key] = $this->applySanitizationRule($value, $rule);
            } else {
                // Если правило не указано, по умолчанию санируем как текстовое поле для безопасности.
                $sanitizedData[$key] = $this->applySanitizationRule($value, SanitizerRule::TEXT_FIELD);
            }
        }

        return new ClearedData($sanitizedData);
    }

    private function applySanitizationRule(mixed $value, string $rule): mixed
    {
        // Рекурсивно обрабатываем массивы, если правило предназначено для скалярного типа.
        // Это позволяет применять одно правило ко всем элементам вложенного массива.
        if (is_array($value)) {
            return array_map(fn($item) => $this->applySanitizationRule($item, $rule), $value);
        }

        return match ($rule) {
            SanitizerRule::EMAIL => $this->stringFilter->sanitizeEmail((string)$value),
            SanitizerRule::URL => $this->stringFilter->sanitizeUrl($value),
            SanitizerRule::INT => (int)$value,
            SanitizerRule::FLOAT => (float)$value,
            SanitizerRule::BOOL => (bool)$value,
            SanitizerRule::KSES_POST => $this->stringFilter->ksesPost($value),
            SanitizerRule::KSES_DATA => $this->stringFilter->ksesData($value),
            SanitizerRule::NO_HTML => $this->stringFilter->stripAllTags($value),
            SanitizerRule::SLUG => $this->stringFilter->sanitizeTitle($value),
            SanitizerRule::KEY => $this->stringFilter->sanitizeKey($value),
            SanitizerRule::FILE_NAME => $this->stringFilter->sanitizeFileName($value),
            SanitizerRule::HTML_CLASS => $this->stringFilter->sanitizeHtmlClass($value),
            SanitizerRule::USER => $this->stringFilter->sanitizeUser($value),
            default => $this->stringFilter->sanitizeTextField($value), // По умолчанию для неизвестных правил
        };
    }
}