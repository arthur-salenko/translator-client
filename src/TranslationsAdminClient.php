<?php

declare(strict_types=1);

namespace ArthurSalenko\TranslatorClient;

use ArthurSalenko\TranslatorClient\Dto\TranslationItem;
use ArthurSalenko\TranslatorClient\Dto\UpsertResult;
use ArthurSalenko\TranslatorClient\Http\HttpTransport;

final class TranslationsAdminClient
{
    public function __construct(private readonly HttpTransport $http)
    {
    }

    /**
     * @param array<int,TranslationItem> $items
     */
    public function upsert(string $lang = 'en', array $items, string $target = 'brand'): UpsertResult
    {
        $payloadItems = [];
        foreach ($items as $item) {
            $payloadItems[] = $item->toArray();
        }

        $json = $this->http->requestJson('PUT', '/v1/admin/translations', [], [
            'lang' => $lang,
            'target' => $target,
            'items' => $payloadItems,
        ]);

        return UpsertResult::fromResponse($json);
    }
}
