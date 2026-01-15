<?php

declare(strict_types=1);

namespace ArthurSalenko\TranslatorClient;

use ArthurSalenko\TranslatorClient\Http\HttpTransport;

final class LanguagesClient
{
    public function __construct(private readonly HttpTransport $http)
    {
    }

    /**
     * @return array{data:list<array{code:string,name:string,is_enabled:bool,sort_order:int}>}
     */
    public function index(): array
    {
        return $this->http->requestJson('GET', '/v1/languages');
    }
}
