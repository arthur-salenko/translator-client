<?php

declare(strict_types=1);

namespace ArthurSalenko\TranslatorClient;

use ArthurSalenko\TranslatorClient\Http\HttpTransport;

final class BrandsAdminClient
{
    public function __construct(private readonly HttpTransport $http)
    {
    }

    /**
     * @return array{data:array{id:int,code:string,name:?string,brand_key:string,is_enabled:bool,created_at:?string,updated_at:?string}}
     */
    public function create(string $code, ?string $name = null, bool $isEnabled = true): array
    {
        return $this->http->requestJson('POST', '/v1/admin/brands', [], [
            'code' => $code,
            'name' => $name,
            'is_enabled' => $isEnabled,
        ]);
    }
}
