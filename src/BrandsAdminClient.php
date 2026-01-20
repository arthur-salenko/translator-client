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

    /**
     * @return array{data:array{id:int,code:string,name:?string,is_enabled:bool}}
     */
    public function update(string $code, ?string $name = null, ?bool $isEnabled = null): array
    {
        $path = sprintf('/v1/admin/brands/%s', rawurlencode($code));

        $payload = [];
        if ($name !== null) {
            $payload['name'] = $name;
        }
        if ($isEnabled !== null) {
            $payload['is_enabled'] = $isEnabled;
        }

        return $this->http->requestJson('PUT', $path, [], $payload);
    }
}
