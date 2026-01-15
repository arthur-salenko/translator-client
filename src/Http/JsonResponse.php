<?php

declare(strict_types=1);

namespace ArthurSalenko\TranslatorClient\Http;

final class JsonResponse
{
    /**
     * @param array<string, list<string>> $headers
     * @param array<string, mixed>|list<mixed>|null $json
     */
    public function __construct(
        public readonly int $statusCode,
        public readonly array $headers,
        public readonly ?array $json,
        public readonly ?string $rawBody,
    ) {
    }

    public function header(string $name): ?string
    {
        foreach ($this->headers as $k => $values) {
            if (strcasecmp($k, $name) === 0) {
                return $values[0] ?? null;
            }
        }

        return null;
    }
}
