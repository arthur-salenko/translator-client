<?php

declare(strict_types=1);

namespace ArthurSalenko\TranslatorClient\Dto;

final class TranslationItem
{
    public function __construct(
        public readonly string  $folder,
        public readonly string  $key,
        public readonly ?string $value,
        public readonly ?string $note = null,
    )
    {
    }

    public function toArray(): array
    {
        return [
            'folder' => $this->folder,
            'key' => $this->key,
            'value' => $this->value,
            'note' => $this->note,
        ];
    }
}
