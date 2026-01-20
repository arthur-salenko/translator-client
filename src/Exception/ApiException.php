<?php

declare(strict_types=1);

namespace ArthurSalenko\TranslatorClient\Exception;

use RuntimeException;

final class ApiException extends RuntimeException
{
    public function __construct(
        string                  $message,
        public readonly int     $statusCode,
        public readonly ?string $responseBody = null,
        public readonly ?array  $responseJson = null,
        ?\Throwable             $previous = null,
    )
    {
        parent::__construct($message, $statusCode, $previous);
    }
}
