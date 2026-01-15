<?php

declare(strict_types=1);

namespace ArthurSalenko\TranslatorClient\Dto;

final class TranslationItem
{
    public function __construct(
        public readonly string $category,
        public readonly string $key,
        public readonly ?string $value,
    ) {
    }

    public function toArray(): array
    {
        return [
            'category' => $this->category,
            'key' => $this->key,
            'value' => $this->value,
        ];
    }
}
