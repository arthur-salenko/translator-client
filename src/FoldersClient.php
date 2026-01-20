<?php

declare(strict_types=1);

namespace ArthurSalenko\TranslatorClient;

use ArthurSalenko\TranslatorClient\Http\HttpTransport;

final class FoldersClient
{
    public function __construct(private readonly HttpTransport $http)
    {
    }

    /**
     * @return array{data:list<array{id:int,code:string,name:string,size:int}>}
     */
    public function index(): array
    {
        return $this->http->requestJson('GET', '/v1/folders');
    }
}
