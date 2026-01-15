<?php

declare(strict_types=1);

namespace ArthurSalenko\TranslatorClient;

use ArthurSalenko\TranslatorClient\Http\HttpTransport;

final class HealthClient
{
    public function __construct(private readonly HttpTransport $http)
    {
    }

    /**
     * @return array{status:string, service:string, timestamp:string}
     */
    public function get(): array
    {
        return $this->http->requestJson('GET', '/v1/health');
    }
}
