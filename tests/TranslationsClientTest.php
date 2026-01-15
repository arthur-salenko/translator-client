<?php

declare(strict_types=1);

namespace ArthurSalenko\TranslatorClient\Tests;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use ArthurSalenko\TranslatorClient\ClientConfig;
use ArthurSalenko\TranslatorClient\Dto\TranslationItem;
use ArthurSalenko\TranslatorClient\Exception\ApiException;
use ArthurSalenko\TranslatorClient\TranslatorClient;
use PHPUnit\Framework\TestCase;

final class TranslationsClientTest extends TestCase
{
    public function testUpsertReturnsResult(): void
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'data' => [
                    'brand_code' => 'doncoupon_ru',
                    'base_revision' => '1700000000000',
                    'brand_revision' => '1700000123000',
                    'effective_revision' => 'a1b2c3',
                    'inserted_to_base' => 3,
                ],
            ])),
        ]);

        $guzzle = new GuzzleClient(['handler' => HandlerStack::create($mock)]);

        $client = new TranslatorClient(new ClientConfig('https://example.test', 'token'), $guzzle);

        $res = $client->translations()->upsert('ru', [
            new TranslationItem('common', 'sitename', 'Hello'),
        ]);

        self::assertSame('doncoupon_ru', $res->brandCode);
        self::assertSame('1700000000000', $res->baseRevision);
        self::assertSame(3, $res->insertedToBase);
    }

    public function testUpsertThrowsApiExceptionOnUnauthorized(): void
    {
        $mock = new MockHandler([
            new Response(401, ['Content-Type' => 'application/json'], json_encode([
                'message' => 'Unauthorized',
            ])),
        ]);

        $guzzle = new GuzzleClient(['handler' => HandlerStack::create($mock)]);

        $client = new TranslatorClient(new ClientConfig('https://example.test', 'token'), $guzzle);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Unauthorized');

        $client->translations()->upsert('ru', [
            new TranslationItem('common', 'sitename', 'Hello'),
        ]);
    }

    public function testRevisionReturnsArray(): void
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'data' => [
                    'brand_code' => 'doncoupon_ru',
                    'base_revision' => '1700000000000',
                    'brand_revision' => '1700000123000',
                    'effective_revision' => 'a1b2c3',
                ],
            ])),
        ]);

        $guzzle = new GuzzleClient(['handler' => HandlerStack::create($mock)]);
        $client = new TranslatorClient(new ClientConfig('https://example.test', 'token'), $guzzle);

        $res = $client->translations()->revision();
        self::assertSame('doncoupon_ru', $res['data']['brand_code']);
        self::assertSame('a1b2c3', $res['data']['effective_revision']);
    }

    public function testShowReturnsValueAndRevision(): void
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'revision' => 'a1b2c3',
                'value' => 'Hello',
            ])),
        ]);

        $guzzle = new GuzzleClient(['handler' => HandlerStack::create($mock)]);
        $client = new TranslatorClient(new ClientConfig('https://example.test', 'token'), $guzzle);

        $res = $client->translations()->show('common', 'sitename', 'ru');
        self::assertSame('a1b2c3', $res->revision);
        self::assertSame('Hello', $res->value);
    }

    public function testCategoriesResponseSupportsNotModified304(): void
    {
        $mock = new MockHandler([
            new Response(304, ['ETag' => 'W/"abc"']),
        ]);

        $guzzle = new GuzzleClient(['handler' => HandlerStack::create($mock)]);
        $client = new TranslatorClient(new ClientConfig('https://example.test', 'token'), $guzzle);

        $res = $client->translations()->categoriesResponse(scope: 'merged', ifNoneMatch: 'W/"abc"');
        self::assertSame(304, $res->statusCode);
        self::assertSame('W/"abc"', $res->header('ETag'));
        self::assertNull($res->json);
    }

    public function testIndexResponseReturnsJsonAndEtag(): void
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json', 'ETag' => 'W/"xyz"'], json_encode([
                'revision' => 'a1b2c3',
                'data' => [
                    'common' => [
                        'sitename' => 'Hello',
                    ],
                ],
            ])),
        ]);

        $guzzle = new GuzzleClient(['handler' => HandlerStack::create($mock)]);
        $client = new TranslatorClient(new ClientConfig('https://example.test', 'token'), $guzzle);

        $res = $client->translations()->indexResponse(lang: 'ru');
        self::assertSame(200, $res->statusCode);
        self::assertSame('W/"xyz"', $res->header('ETag'));
        self::assertIsArray($res->json);
        self::assertSame('a1b2c3', $res->json['revision']);
    }
}
