<?php

namespace UserSpace\Core\Exception;

use Exception;
use WP_Error;

class UspException extends Exception implements ExceptionInterface
{
    protected $code;
    protected $message;
    private readonly mixed $data;

    public function __construct(string $message, string $code, mixed $data = null)
    {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
    }

    /**
     * Трансформирует объект WP_Error в экземпляр текущего класса.
     */
    public static function createFromWpError(WP_Error $wpError): self
    {
        return new self(
            $wpError->get_error_message(),
            (int) $wpError->get_error_code(),
            $wpError->get_error_data()
        );
    }

    public static function isWpError(mixed $thing): bool
    {
        return $thing instanceof WP_Error;
    }

    public function getData(): mixed
    {
        return $this->data;
    }
}