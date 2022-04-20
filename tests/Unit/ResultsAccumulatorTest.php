<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\ResultsAccumulator;
use Tests\TestCase;

class ResultsAccumulatorTest extends TestCase
{
    public function testIncrement(): ResultsAccumulator
    {
        $stringId = 'fake_test';
        $accumulator = new ResultsAccumulator();

        $accumulator->increment($stringId);

        $this->assertEquals(1, $accumulator->getCount($stringId));

        $accumulator->increment($stringId);
        $this->assertEquals(2, $accumulator->getCount($stringId));

        return $accumulator;
    }

    /**
     * @depends testIncrement
     */
    public function testCanHaveMultipleCounts(ResultsAccumulator $accumulator): void
    {
        $accumulator->increment('new_fake_count_1');
        $accumulator->increment('new_fake_count_2');

        $this->assertArrayHasKey('new_fake_count_1', $accumulator->getCounts());
        $this->assertArrayHasKey('new_fake_count_2', $accumulator->getCounts());
    }

    public function testAddError(): void
    {
        $accumulator = new ResultsAccumulator();

        $accumulator->addError(['error' => 'message']);

        $this->assertEquals([['error' => 'message']], $accumulator->getErrors());
    }
}