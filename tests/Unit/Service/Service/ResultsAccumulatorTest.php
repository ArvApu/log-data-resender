<?php

declare(strict_types=1);

namespace Tests\Unit\Service\Service;

use App\Service\Sender\ResultsAccumulator;
use PHPUnit\Framework\Attributes\Depends;
use Tests\UnitTestCase;

class ResultsAccumulatorTest extends UnitTestCase
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

    #[Depends('testIncrement')]
    public function testCanHaveMultipleCounts(ResultsAccumulator $accumulator): void
    {
        $accumulator->increment('new_fake_count_1');
        $accumulator->increment('new_fake_count_2');

        $this->assertArrayHasKey('new_fake_count_1', $accumulator->getCounts());
        $this->assertArrayHasKey('new_fake_count_2', $accumulator->getCounts());
    }
}