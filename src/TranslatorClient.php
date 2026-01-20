<?php

declare(strict_types=1);

namespace ArthurSalenko\TranslatorClient;

use ArthurSalenko\TranslatorClient\Http\HttpTransport;
use GuzzleHttp\Client as GuzzleClient;

final class TranslatorClient
{
    private HttpTransport $http;

    public function __construct(public readonly ClientConfig $config, ?GuzzleClient $guzzle = null)
    {
        $this->http = new HttpTransport($this->config, $guzzle);
    }

    public function health(): HealthClient
    {
        return new HealthClient($this->http);
    }

    public function languages(): LanguagesClient
    {
        return new LanguagesClient($this->http);
    }

    public function brands(): BrandsClient
    {
        return new BrandsClient($this->http);
    }

    public function folders(): FoldersClient
    {
        return new FoldersClient($this->http);
    }

    public function translations(): TranslationsReadClient
    {
        return new TranslationsReadClient($this->http);
    }

    public function admin(): AdminClient
    {
        return new AdminClient($this->http);
    }

}
