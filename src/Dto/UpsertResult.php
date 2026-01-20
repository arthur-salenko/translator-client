<?php

declare(strict_types=1);

namespace ArthurSalenko\TranslatorClient\Dto;

final class UpsertResult
{
    public function __construct(
        public readonly string  $brandCode,
        public readonly ?string $baseRevision,
        public readonly ?string $brandRevision,
        public readonly ?string $effectiveRevision,
        public readonly int     $insertedToBase,
    )
    {
    }

    public static function fromResponse(array $json): self
    {
        $data = isset($json['data']) && is_array($json['data']) ? $json['data'] : [];

        return new self(
            brandCode: (string)($data['brand_code'] ?? ''),
            baseRevision: isset($data['base_revision']) ? (string)$data['base_revision'] : null,
            brandRevision: isset($data['brand_revision']) ? (string)$data['brand_revision'] : null,
            effectiveRevision: isset($data['effective_revision']) ? (string)$data['effective_revision'] : null,
            insertedToBase: (int)($data['inserted_to_base'] ?? 0),
        );
    }
}
