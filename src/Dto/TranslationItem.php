<?php

declare(strict_types=1);

namespace ArthurSalenko\TranslatorClient\Dto;

final class TranslationItem
{
    /**
     * @param array<string, string|null> $values
     */
    public function __construct(
        public readonly string  $folder,
        public readonly string  $key,
        public readonly array   $values,
        public readonly ?string $note = null,
    )
    {
    }

    public function toArray(): array
    {
        $out = [
            'folder' => $this->folder,
            'key' => $this->key,
            'values' => $this->values,
        ];

        if ($this->note !== null) {
            $out['note'] = $this->note === '' ? null : $this->note;
        }

        return $out;
    }
}
