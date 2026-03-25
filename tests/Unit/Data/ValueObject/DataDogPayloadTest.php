<?php

declare(strict_types=1);

namespace Tests\Unit\Data\ValueObject;

use App\Data\ValueObject\DataDogPayload;
use Tests\UnitTestCase;

class DataDogPayloadTest extends UnitTestCase
{
    public function testFromArrayHandlesMissingFilter(): void
    {
        $payload = DataDogPayload::fromArray([]);

        $this->assertSame(['filter' => []], $payload->toArray());
    }

    public function testEnsureRangeAddsMissingDates(): void
    {
        $payload = new DataDogPayload('service:app');

        $result = $payload->ensureRange([
            'from' => '2024-01-01T00:00:00+00:00',
            'to' => '2024-01-01T01:00:00+00:00',
        ]);

        $this->assertNotSame($payload, $result);
        $this->assertSame([
            'filter' => [
                'query' => 'service:app',
                'from' => '2024-01-01T00:00:00+00:00',
                'to' => '2024-01-01T01:00:00+00:00',
            ],
        ], $result->toArray());
    }

    public function testEnsureRangeReturnsSameInstanceWhenRangeIsPresent(): void
    {
        $payload = new DataDogPayload(
            'service:app',
            '2024-01-01T00:00:00+00:00',
            '2024-01-01T01:00:00+00:00'
        );

        $result = $payload->ensureRange([
            'from' => '2024-01-02T00:00:00+00:00',
            'to' => '2024-01-02T01:00:00+00:00',
        ]);

        $this->assertSame($payload, $result);
    }
}
