<?php

declare(strict_types=1);

namespace Tests\Unit\Service\DataDog;

use App\Service\DataDog\DataDogRangeResolver;
use Tests\UnitTestCase;

class DataDogRangeResolverTest extends UnitTestCase
{
    public function testNormalizeTimeValueWithEpochSeconds(): void
    {
        $resolver = new DataDogRangeResolver();
        $epoch = '1700000000';

        $expected = new \DateTimeImmutable("@{$epoch}")
            ->setTimezone(new \DateTimeZone('UTC'))
            ->format(DATE_ATOM);

        $this->assertSame($expected, $resolver->normalizeTimeValue($epoch));
    }

    public function testNormalizeTimeValueWithEpochMilliseconds(): void
    {
        $resolver = new DataDogRangeResolver();
        $epochMs = '1700000000000';
        $epochSeconds = (int) ($epochMs / 1000);

        $expected = new \DateTimeImmutable("@{$epochSeconds}")
            ->setTimezone(new \DateTimeZone('UTC'))
            ->format(DATE_ATOM);

        $this->assertSame($expected, $resolver->normalizeTimeValue($epochMs));
    }

    public function testNormalizeTimeValueWithDateString(): void
    {
        $resolver = new DataDogRangeResolver();
        $input = '2024-01-02T03:04:05+02:00';

        $expected = new \DateTimeImmutable($input)
            ->setTimezone(new \DateTimeZone('UTC'))
            ->format(DATE_ATOM);

        $this->assertSame($expected, $resolver->normalizeTimeValue($input));
    }

    public function testNormalizeTimeValueWithInvalidInputReturnsNull(): void
    {
        $resolver = new DataDogRangeResolver();

        $this->assertNull($resolver->normalizeTimeValue('not-a-date'));
    }

    public function testResolveCustomRangeUsesNormalizedValues(): void
    {
        $resolver = new DataDogRangeResolver();

        $range = $resolver->resolve(null, '1700000000', '1700003600');

        $expectedFrom = new \DateTimeImmutable('@1700000000')
            ->setTimezone(new \DateTimeZone('UTC'))
            ->format(DATE_ATOM);
        $expectedTo = new \DateTimeImmutable('@1700003600')
            ->setTimezone(new \DateTimeZone('UTC'))
            ->format(DATE_ATOM);

        $this->assertSame([
            'from' => $expectedFrom,
            'to' => $expectedTo,
        ], $range);
    }

    public function testResolveCustomRangeRequiresBothDates(): void
    {
        $resolver = new DataDogRangeResolver();

        $this->expectException(\InvalidArgumentException::class);
        $resolver->resolve(null, '1700000000', null);
    }

    public function testResolvePresetReturnsHourRange(): void
    {
        $resolver = new DataDogRangeResolver();

        $range = $resolver->resolve('last_1h', null, null);

        $this->assertIsArray($range);

        $from = new \DateTimeImmutable($range['from']);
        $to = new \DateTimeImmutable($range['to']);

        $this->assertSame(3600, $to->getTimestamp() - $from->getTimestamp());
    }

    public function testResolveReturnsNullForInvalidPreset(): void
    {
        $resolver = new DataDogRangeResolver();

        $this->assertNull($resolver->resolve('not-a-preset', null, null));
    }
}
