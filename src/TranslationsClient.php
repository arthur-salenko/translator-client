<?php

declare(strict_types=1);

namespace ArthurSalenko\TranslatorClient;

use ArthurSalenko\TranslatorClient\Dto\TranslationItem;
use ArthurSalenko\TranslatorClient\Dto\TranslationValueResult;
use ArthurSalenko\TranslatorClient\Dto\UpsertResult;
use ArthurSalenko\TranslatorClient\Http\HttpTransport;
use ArthurSalenko\TranslatorClient\Http\JsonResponse;

final class TranslationsClient
{
    public function __construct(private readonly HttpTransport $http)
    {
    }

    /**
     * @return array{data:array{brand_code:string,base_revision?:string,brand_revision?:string,effective_revision?:string}}
     */
    public function revision(): array
    {
        return $this->http->requestJson('GET', '/v1/translations/revision');
    }

    public function indexResponse(
        string  $lang = 'en',
        ?string $folder = null,
        string  $format = 'tree',
        string  $scope = 'merged',
        ?string $ifNoneMatch = null,
    ): JsonResponse
    {
        $headers = [];
        if ($ifNoneMatch !== null && $ifNoneMatch !== '') {
            $headers['If-None-Match'] = $ifNoneMatch;
        }

        $query = [
            'lang' => $lang,
            'format' => $format,
            'scope' => $scope,
        ];
        if ($folder !== null && $folder !== '') {
            $query['folder'] = $folder;
        }

        return $this->http->requestJsonResponse('GET', '/v1/translations', $query, null, $headers);
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

    public function show(string $folder, string $key, string $lang = 'en'): TranslationValueResult
    {
        $path = sprintf(
            '/v1/translations/%s/%s',
            rawurlencode($folder),
            rawurlencode($key),
        );

        $json = $this->http->requestJson('GET', $path, [
            'lang' => $lang,
        ]);

        return TranslationValueResult::fromResponse($json);
    }
}
