<?php

declare(strict_types=1);

namespace ArthurSalenko\TranslatorClient\Http;

use ArthurSalenko\TranslatorClient\ClientConfig;
use ArthurSalenko\TranslatorClient\Exception\ApiException;
use ArthurSalenko\TranslatorClient\Exception\NetworkException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

final class HttpTransport
{
    private Client $client;

    public function __construct(private readonly ClientConfig $config, ?Client $client = null)
    {
        $this->client = $client ?? new Client([
            'base_uri' => rtrim($this->config->baseUrl, '/') . '/',
            'timeout' => $this->config->timeoutSeconds,
            'connect_timeout' => $this->config->connectTimeoutSeconds,
            'http_errors' => false,
        ]);
    }

    public function requestJson(string $method, string $path, array $query = [], ?array $json = null, array $headers = []): array
    {
        $response = $this->request($method, $path, $query, $json, $headers);

        $body = (string)$response->getBody();
        if ($body === '') {
            return [];
        }

        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            throw new ApiException('Invalid JSON response', $response->getStatusCode(), $body, null);
        }

        return $decoded;
    }

    public function requestJsonResponse(string $method, string $path, array $query = [], ?array $json = null, array $headers = []): JsonResponse
    {
        $response = $this->request($method, $path, $query, $json, $headers);

        $body = (string)$response->getBody();
        if ($body === '') {
            return new JsonResponse(
                statusCode: $response->getStatusCode(),
                headers: $response->getHeaders(),
                json: null,
                rawBody: null,
            );
        }

        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            throw new ApiException('Invalid JSON response', $response->getStatusCode(), $body, null);
        }

        return new JsonResponse(
            statusCode: $response->getStatusCode(),
            headers: $response->getHeaders(),
            json: $decoded,
            rawBody: $body,
        );
    }

    public function request(string $method, string $path, array $query = [], ?array $json = null, array $headers = []): ResponseInterface
    {
        $headers = $this->applyBrandKeyHeader($headers);

        $options = [
            'headers' => $this->buildHeaders($headers),
            'query' => $query,
        ];

        if ($json !== null) {
            $options['json'] = $json;
        }

        try {
            $response = $this->client->request($method, ltrim($path, '/'), $options);
        } catch (ConnectException $e) {
            throw new NetworkException($e->getMessage(), (int)$e->getCode(), $e);
        } catch (RequestException $e) {
            $resp = $e->getResponse();
            if ($resp !== null) {
                throw $this->buildApiExceptionFromResponse($resp, $e);
            }

            throw new NetworkException($e->getMessage(), (int)$e->getCode(), $e);
        } catch (GuzzleException $e) {
            throw new NetworkException($e->getMessage(), (int)$e->getCode(), $e);
        }

        $status = $response->getStatusCode();
        if ($status >= 400) {
            throw $this->buildApiExceptionFromResponse($response, null);
        }

        return $response;
    }

    private function buildApiExceptionFromResponse(ResponseInterface $response, ?RequestException $exception): ApiException
    {
        $status = $response->getStatusCode();
        $body = (string)$response->getBody();
        $json = $this->decodeJsonOrNull($body);

        $message = 'Translator API error';
        if (is_array($json) && isset($json['message']) && is_string($json['message'])) {
            $message = $json['message'];
        }

        if ($exception !== null) {
            return new ApiException($message, $status, $body !== '' ? $body : null, $json, $exception);
        }

        return new ApiException($message, $status, $body !== '' ? $body : null, $json);
    }

    private function decodeJsonOrNull(string $body): ?array
    {
        if ($body === '') {
            return null;
        }

        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            return null;
        }

        return $decoded;
    }

    private function buildHeaders(array $headers): array
    {
        $base = [
            'Accept' => 'application/json',
        ];

        if ($this->config->userAgent !== null) {
            $base['User-Agent'] = $this->config->userAgent;
        }

        foreach ($headers as $k => $v) {
            $base[$k] = $v;
        }

        return $base;
    }

    private function applyBrandKeyHeader(array $headers): array
    {
        if ($this->config->brandKey === null || $this->config->brandKey === '') {
            return $headers;
        }

        if (!array_key_exists('X-Brand-Key', $headers)) {
            $headers['X-Brand-Key'] = $this->config->brandKey;
        }

        return $headers;
    }
}
