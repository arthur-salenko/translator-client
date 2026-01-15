# arthur-salenko/translator-client

PHP SDK for the `translator` service.

## Requirements

- PHP `^8.1`

## Installation

```bash
composer require arthur-salenko/translator-client
```

## Usage

```php
use ArthurSalenko\TranslatorClient\ClientConfig;
use ArthurSalenko\TranslatorClient\Dto\TranslationItem;
use ArthurSalenko\TranslatorClient\TranslatorClient;

$client = new TranslatorClient(new ClientConfig(
  baseUrl: 'https://translator.my-domain.tld',
  token: 'YOUR_BEARER_TOKEN',
));

// Health
$health = $client->health()->get();

// Languages
$languages = $client->languages()->index();

// Upsert translations
$result = $client->translations()->upsert(lang: 'ru', items: [
  new TranslationItem('common', 'sitename', 'Привет'),
]);
```

### Configuration

```php
use ArthurSalenko\TranslatorClient\ClientConfig;
use ArthurSalenko\TranslatorClient\TranslatorClient;

$config = new ClientConfig(
  baseUrl: 'https://translator.my-domain.tld',
  token: 'YOUR_BEARER_TOKEN',
  timeoutSeconds: 10.0,
  connectTimeoutSeconds: 5.0,
  userAgent: 'my-app/1.0',
);

$client = new TranslatorClient($config);
```

### Custom Guzzle client

```php
use ArthurSalenko\TranslatorClient\ClientConfig;
use ArthurSalenko\TranslatorClient\TranslatorClient;
use GuzzleHttp\Client as GuzzleClient;

$guzzle = new GuzzleClient([
  'verify' => false,
]);

$client = new TranslatorClient(
  new ClientConfig('https://translator.my-domain.tld', 'YOUR_BEARER_TOKEN'),
  $guzzle,
);
```

## Translations API

Unless specified explicitly, methods that accept `lang` default to `en`.

### Revisions

```php
$revs = $client->translations()->revision();
```

### Get translations (JSON + headers)

`indexResponse()` returns a response object with:

- `statusCode`
- `headers`
- `json`
- `rawBody`

```php
$response = $client->translations()->indexResponse(lang: 'ru');

if ($response->statusCode === 200) {
  $revision = $response->json['revision'] ?? null;
  $data = $response->json['data'] ?? [];
}
```

### Categories / Translations (ETag)

Service endpoints `categories` and `index` may return `304 Not Modified`.
The SDK provides methods that return status + headers:

```php
$response = $client->translations()->categoriesResponse(scope: 'merged', ifNoneMatch: $etag);

if ($response->statusCode === 304) {
  // use cached response
} else {
  $etag = $response->header('ETag');
  $revision = $response->json['revision'] ?? null;
  $categories = $response->json['data'] ?? [];
}
```

Same pattern for `indexResponse()`:

```php
$response = $client->translations()->indexResponse(
  lang: 'ru',
  category: 'common',
  format: 'tree',
  scope: 'merged',
  ifNoneMatch: $etag,
);

if ($response->statusCode === 304) {
  // use cached response
} else {
  $etag = $response->header('ETag');
  $revision = $response->json['revision'] ?? null;
  $data = $response->json['data'] ?? [];
}
```

### Get translation value

```php
$value = $client->translations()->show(category: 'common', key: 'sitename');
// service response: { revision: string, value: mixed|null }
```

### Upsert translations

```php
use ArthurSalenko\TranslatorClient\Dto\TranslationItem;

$res = $client->translations()->upsert(
  lang: 'ru',
  items: [
    new TranslationItem('common', 'sitename', 'Hello'),
    new TranslationItem('common', 'title', 'Title'),
  ],
  target: 'brand',
);

$brandCode = $res->brandCode;
$baseRevision = $res->baseRevision;
$brandRevision = $res->brandRevision;
$effectiveRevision = $res->effectiveRevision;
$insertedToBase = $res->insertedToBase;
```

## Errors

- `ArthurSalenko\TranslatorClient\Exception\ApiException` — HTTP `4xx/5xx` (including parsed JSON, if available)
- `ArthurSalenko\TranslatorClient\Exception\NetworkException` — network/transport errors

```php
use ArthurSalenko\TranslatorClient\Exception\ApiException;
use ArthurSalenko\TranslatorClient\Exception\NetworkException;

try {
  $client->translations()->upsert(lang: 'ru', items: [
    new TranslationItem('common', 'sitename', 'Hello'),
  ]);
} catch (ApiException $e) {
  $status = $e->statusCode;
  $raw = $e->responseBody;
  $json = $e->responseJson;
} catch (NetworkException $e) {
  // connection errors, timeouts, etc
}
