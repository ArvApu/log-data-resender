<?php

declare(strict_types=1);

namespace Tests\Unit\Service\DataDog;

use App\Service\DataDog\DataDogFilterNormalizer;
use App\Service\DataDog\DataDogRangeResolver;
use Tests\UnitTestCase;

class DataDogFilterNormalizerTest extends UnitTestCase
{
    public function testNormalizeRequiresFilter(): void
    {
        $normalizer = new DataDogFilterNormalizer(new DataDogRangeResolver());

        $this->expectException(\InvalidArgumentException::class);
        $normalizer->normalize('   ', null, null, null);
    }

    public function testNormalizeAddsRangeToQueryFilter(): void
    {
        $normalizer = new DataDogFilterNormalizer(new DataDogRangeResolver());

        $normalized = $normalizer->normalize('service:app', null, '1700000000', '1700003600');
        $decoded = json_decode($normalized, true);

        $expectedFrom = new \DateTimeImmutable('@1700000000')
            ->setTimezone(new \DateTimeZone('UTC'))
            ->format(DATE_ATOM);

        $expectedTo = new \DateTimeImmutable('@1700003600')
            ->setTimezone(new \DateTimeZone('UTC'))
            ->format(DATE_ATOM);

        $this->assertSame([
            'filter' => [
                'query' => 'service:app',
                'from' => $expectedFrom,
                'to' => $expectedTo,
            ],
        ], $decoded);
    }

    public function testNormalizeParsesCurlPayload(): void
    {
        $normalizer = new DataDogFilterNormalizer(new DataDogRangeResolver());

        $payload = json_encode([
            'filter' => [
                'query' => 'status:error',
            ],
        ], JSON_THROW_ON_ERROR);

        $curl = "curl https://api.datadoghq.com/api/v2/logs/events/search --data '{$payload}'";

        $normalized = $normalizer->normalize($curl, null, null, null);

        $this->assertSame(
            json_decode($payload, true),
            json_decode($normalized, true)
        );
    }

    public function testNormalizeParsesUrlPayload(): void
    {
        $normalizer = new DataDogFilterNormalizer(new DataDogRangeResolver());

        $url = 'https://app.datadoghq.com/logs?query=service%3Aapp&from_ts=1700000000&to_ts=1700003600';

        $normalized = $normalizer->normalize($url, null, null, null);

        $expectedFrom = new \DateTimeImmutable('@1700000000')
            ->setTimezone(new \DateTimeZone('UTC'))
            ->format(DATE_ATOM);

        $expectedTo = new \DateTimeImmutable('@1700003600')
            ->setTimezone(new \DateTimeZone('UTC'))
            ->format(DATE_ATOM);

        $this->assertSame([
            'filter' => [
                'query' => 'service:app',
                'from' => $expectedFrom,
                'to' => $expectedTo,
            ],
        ], json_decode($normalized, true));
    }

    public function testNormalizeDoesNotOverrideExistingRange(): void
    {
        $normalizer = new DataDogFilterNormalizer(new DataDogRangeResolver());

        $payload = [
            'filter' => [
                'query' => 'service:app',
                'from' => '2024-01-01T00:00:00+00:00',
                'to' => '2024-01-01T01:00:00+00:00',
            ],
        ];

        $normalized = $normalizer->normalize(
            json_encode($payload, JSON_THROW_ON_ERROR),
            'last_1h',
            null,
            null
        );

        $this->assertSame($payload, json_decode($normalized, true));
    }
}
