<?php

declare(strict_types=1);

namespace ArthurSalenko\TranslatorClient\Tests;

use ArthurSalenko\TranslatorClient\ClientConfig;
use ArthurSalenko\TranslatorClient\Dto\TranslationItem;
use ArthurSalenko\TranslatorClient\Exception\ApiException;
use ArthurSalenko\TranslatorClient\TranslatorClient;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
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

        $stack = HandlerStack::create($mock);
        $stack->push(static function (callable $handler) {
            return static function ($request, array $options) use ($handler) {
                TestCase::assertSame('brand-key', $request->getHeaderLine('X-Brand-Key'));
                TestCase::assertSame('', $request->getUri()->getQuery());
                return $handler($request, $options);
            };
        });

        $guzzle = new GuzzleClient(['handler' => $stack]);

        $client = new TranslatorClient(new ClientConfig('https://example.test', 'brand-key'), $guzzle);

        $res = $client->admin()->translations()->upsert('ru', [
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

        $stack = HandlerStack::create($mock);
        $stack->push(static function (callable $handler) {
            return static function ($request, array $options) use ($handler) {
                TestCase::assertSame('brand-key', $request->getHeaderLine('X-Brand-Key'));
                return $handler($request, $options);
            };
        });

        $guzzle = new GuzzleClient(['handler' => $stack]);

        $client = new TranslatorClient(new ClientConfig('https://example.test', 'brand-key'), $guzzle);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Unauthorized');

        $client->admin()->translations()->upsert('ru', [
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

        $stack = HandlerStack::create($mock);
        $stack->push(static function (callable $handler) {
            return static function ($request, array $options) use ($handler) {
                TestCase::assertSame('brand-key', $request->getHeaderLine('X-Brand-Key'));
                return $handler($request, $options);
            };
        });

        $guzzle = new GuzzleClient(['handler' => $stack]);
        $client = new TranslatorClient(new ClientConfig('https://example.test', 'brand-key'), $guzzle);

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

        $stack = HandlerStack::create($mock);
        $stack->push(static function (callable $handler) {
            return static function ($request, array $options) use ($handler) {
                TestCase::assertSame('brand-key', $request->getHeaderLine('X-Brand-Key'));
                return $handler($request, $options);
            };
        });

        $guzzle = new GuzzleClient(['handler' => $stack]);
        $client = new TranslatorClient(new ClientConfig('https://example.test', 'brand-key'), $guzzle);

        $res = $client->translations()->show('common', 'sitename', 'ru');
        self::assertSame('a1b2c3', $res->revision);
        self::assertSame('Hello', $res->value);
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

        $stack = HandlerStack::create($mock);
        $stack->push(static function (callable $handler) {
            return static function ($request, array $options) use ($handler) {
                TestCase::assertSame('brand-key', $request->getHeaderLine('X-Brand-Key'));
                return $handler($request, $options);
            };
        });

        $guzzle = new GuzzleClient(['handler' => $stack]);
        $client = new TranslatorClient(new ClientConfig('https://example.test', 'brand-key'), $guzzle);

        $res = $client->translations()->indexResponse(lang: 'ru');
        self::assertSame(200, $res->statusCode);
        self::assertSame('W/"xyz"', $res->header('ETag'));
        self::assertIsArray($res->json);
        self::assertSame('a1b2c3', $res->json['revision']);
    }

    public function testBrandsIndexReturnsArray(): void
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'data' => [
                    [
                        'id' => 1,
                        'code' => 'doncoupon_ru',
                        'name' => 'Doncoupon RU',
                        'is_enabled' => true,
                        'created_at' => '2026-01-16T00:00:00Z',
                        'updated_at' => '2026-01-16T00:00:00Z',
                    ],
                ],
            ])),
        ]);

        $guzzle = new GuzzleClient(['handler' => HandlerStack::create($mock)]);
        $client = new TranslatorClient(new ClientConfig('https://example.test', null), $guzzle);

        $res = $client->brands()->index();
        self::assertIsArray($res);
        self::assertSame('doncoupon_ru', $res['data'][0]['code']);
    }

    public function testBrandsAdminCreateReturnsBrandKey(): void
    {
        $mock = new MockHandler([
            new Response(201, ['Content-Type' => 'application/json'], json_encode([
                'data' => [
                    'id' => 10,
                    'code' => 'doncoupon_ua',
                    'name' => 'Doncoupon UA',
                    'brand_key' => 'secret',
                    'is_enabled' => true,
                    'created_at' => '2026-01-16T00:00:00Z',
                    'updated_at' => '2026-01-16T00:00:00Z',
                ],
            ])),
        ]);

        $stack = HandlerStack::create($mock);
        $stack->push(static function (callable $handler) {
            return static function ($request, array $options) use ($handler) {
                TestCase::assertSame('admin-brand-key', $request->getHeaderLine('X-Brand-Key'));
                return $handler($request, $options);
            };
        });

        $guzzle = new GuzzleClient(['handler' => $stack]);
        $client = new TranslatorClient(new ClientConfig('https://example.test', 'admin-brand-key'), $guzzle);

        $res = $client->admin()->brands()->create(code: 'doncoupon_ua', name: 'Doncoupon UA');
        self::assertSame('doncoupon_ua', $res['data']['code']);
        self::assertSame('secret', $res['data']['brand_key']);
    }
}
