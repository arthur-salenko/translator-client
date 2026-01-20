<?php

declare(strict_types=1);

namespace ArthurSalenko\TranslatorClient;

use ArthurSalenko\TranslatorClient\Http\HttpTransport;

final class FoldersAdminClient
{
    public function __construct(private readonly HttpTransport $http)
    {
    }

    /**
     * @return array{data:array{id:int,code:string,name:string,size:int}}
     */
    public function create(string $code, string $name): array
    {
        return $this->http->requestJson('POST', '/v1/admin/folders', [], [
            'code' => $code,
            'name' => $name,
        ]);
    }

    /**
     * @return array{data:array{id:int,code:string,name:string,size:int}}
     */
    public function update(string $code, string $name): array
    {
        $path = sprintf('/v1/admin/folders/%s', rawurlencode($code));

        return $this->http->requestJson('PUT', $path, [], [
            'name' => $name,
        ]);
    }

    /**
     * @return array{data:array{deleted:bool}}
     */
    public function delete(string $code): array
    {
        $path = sprintf('/v1/admin/folders/%s', rawurlencode($code));

        return $this->http->requestJson('DELETE', $path);
    }
}
