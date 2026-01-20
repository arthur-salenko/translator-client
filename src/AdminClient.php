<?php

declare(strict_types=1);

namespace ArthurSalenko\TranslatorClient;

use ArthurSalenko\TranslatorClient\Http\HttpTransport;

final class AdminClient
{
    public function __construct(private readonly HttpTransport $http)
    {
    }

    public function brands(): BrandsAdminClient
    {
        return new BrandsAdminClient($this->http);
    }

    public function folders(): FoldersAdminClient
    {
        return new FoldersAdminClient($this->http);
    }

    public function translations(): TranslationsAdminClient
    {
        return new TranslationsAdminClient($this->http);
    }
}
