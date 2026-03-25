<?php

declare(strict_types=1);

namespace Tests\Unit\Service\Service;

use App\Constant\Enum\ResultCategory;
use App\Service\LogResender\Sender\ResultsAccumulator;
use Tests\UnitTestCase;

class ResultsAccumulatorTest extends UnitTestCase
{
    public function testIncrement(): void
    {
        $accumulator = new ResultsAccumulator();

        $accumulator->increment(ResultCategory::COMPLETED);

        $this->assertSame(1, $accumulator->getCount(ResultCategory::COMPLETED));

        $accumulator->increment(ResultCategory::COMPLETED);
        $this->assertSame(2, $accumulator->getCount(ResultCategory::COMPLETED));
    }

    public function testIncrementWithCustomStep(): void
    {
        $accumulator = new ResultsAccumulator();

        $accumulator->increment(ResultCategory::FAILED, 3);

        $this->assertSame(3, $accumulator->getCount(ResultCategory::FAILED));
    }

    public function testCanHaveMultipleCounts(): void
    {
        $accumulator = new ResultsAccumulator();

        $accumulator->increment(ResultCategory::COMPLETED);
        $accumulator->increment(ResultCategory::FAILED);

        $this->assertArrayHasKey(ResultCategory::COMPLETED->value, $accumulator->getCounts());
        $this->assertArrayHasKey(ResultCategory::FAILED->value, $accumulator->getCounts());
    }

    public function testSetException(): void
    {
        $accumulator = new ResultsAccumulator();
        $exception = new \RuntimeException('Boom');

        $accumulator->setException($exception);

        $this->assertSame($exception, $accumulator->getException());
    }
}
