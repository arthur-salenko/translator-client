<?php

declare(strict_types=1);

namespace ArthurSalenko\TranslatorClient\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use ArthurSalenko\TranslatorClient\ClientConfig;
use ArthurSalenko\TranslatorClient\Exception\ApiException;
use ArthurSalenko\TranslatorClient\Exception\NetworkException;
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

        $body = (string) $response->getBody();
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

        $body = (string) $response->getBody();
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
            throw new NetworkException($e->getMessage(), (int) $e->getCode(), $e);
        } catch (RequestException $e) {
            $resp = $e->getResponse();
            if ($resp !== null) {
                $status = $resp->getStatusCode();
                $body = (string) $resp->getBody();

                $json = null;
                if ($body !== '') {
                    $decoded = json_decode($body, true);
                    if (is_array($decoded)) {
                        $json = $decoded;
                    }
                }

                $message = 'Translator API error';
                if (is_array($json) && isset($json['message']) && is_string($json['message'])) {
                    $message = $json['message'];
                }

                throw new ApiException($message, $status, $body !== '' ? $body : null, $json, $e);
            }

            throw new NetworkException($e->getMessage(), (int) $e->getCode(), $e);
        } catch (GuzzleException $e) {
            throw new NetworkException($e->getMessage(), (int) $e->getCode(), $e);
        }

        $status = $response->getStatusCode();
        if ($status >= 400) {
            $body = (string) $response->getBody();
            $json = null;
            if ($body !== '') {
                $decoded = json_decode($body, true);
                if (is_array($decoded)) {
                    $json = $decoded;
                }
            }

            $message = 'Translator API error';
            if (is_array($json) && isset($json['message']) && is_string($json['message'])) {
                $message = $json['message'];
            }

            throw new ApiException($message, $status, $body !== '' ? $body : null, $json);
        }

        return $response;
    }

    private function buildHeaders(array $headers): array
    {
        $base = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->config->token,
        ];

        if ($this->config->userAgent !== null) {
            $base['User-Agent'] = $this->config->userAgent;
        }

        foreach ($headers as $k => $v) {
            $base[$k] = $v;
        }

        return $base;
    }
}
