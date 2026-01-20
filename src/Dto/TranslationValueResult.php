<?php

declare(strict_types=1);

namespace ArthurSalenko\TranslatorClient\Dto;

final class TranslationValueResult
{
    public function __construct(
        public readonly string $revision,
        public readonly mixed  $value,
    )
    {
    }

    public static function fromResponse(array $json): self
    {
        return new self(
            revision: (string)($json['revision'] ?? ''),
            value: $json['value'] ?? null,
        );
    }
}
