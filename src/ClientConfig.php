<?php

declare(strict_types=1);

namespace ArthurSalenko\TranslatorClient;

final class ClientConfig
{
    public function __construct(
        public readonly string  $baseUrl,
        public readonly ?string $brandKey,
        public readonly float   $timeoutSeconds = 10.0,
        public readonly float   $connectTimeoutSeconds = 5.0,
        public readonly ?string $userAgent = null,
    )
    {
    }
}
