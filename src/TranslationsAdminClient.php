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

    public function index(string $lang = 'en', ?string $folder = null, string $scope = 'merged'): array
    {
        $query = [
            'lang' => $lang,
            'scope' => $scope,
        ];
        if ($folder !== null && $folder !== '') {
            $query['folder'] = $folder;
        }

        return $this->http->requestJson('GET', '/v1/admin/translations', $query);
    }

    /**
     * @param array<int,TranslationItem> $items
     */
    public function upsert(array $items, string $target = 'brand'): UpsertResult
    {
        $payloadItems = [];
        foreach ($items as $item) {
            $payloadItems[] = $item->toArray();
        }

        $json = $this->http->requestJson('PUT', '/v1/admin/translations', [], [
            'target' => $target,
            'items' => $payloadItems,
        ]);

        return UpsertResult::fromResponse($json);
    }

    public function delete(string $folder, string $key, string $target = 'brand'): array
    {
        $path = sprintf(
            '/v1/admin/translations/%s/%s',
            rawurlencode($folder),
            rawurlencode($key),
        );

        return $this->http->requestJson('DELETE', $path, [
            'target' => $target,
        ]);
    }
}
