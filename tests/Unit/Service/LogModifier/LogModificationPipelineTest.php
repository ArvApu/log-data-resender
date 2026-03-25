<?php

declare(strict_types=1);

namespace Tests\Unit\Service\LogModifier;

use App\Service\LogModifier\LogModificationPipeline;
use App\Service\LogModifier\LogModifierInterface;
use Tests\UnitTestCase;

class LogModificationPipelineTest extends UnitTestCase
{
    public function testProcessSkipsWhenNoEnabledModifiers(): void
    {
        [$pipeline, $modifierA, $modifierB] = $this->createPipeline();

        $input = ['message' => 'original'];
        $output = $pipeline->process($input);

        $this->assertSame($input, $output);
        $this->assertSame(0, $modifierA->called);
        $this->assertSame(0, $modifierB->called);
    }

    public function testProcessAppliesOnlyEnabledModifiers(): void
    {
        [$pipeline, $modifierA, $modifierB] = $this->createPipeline();

        $pipeline->setEnabledModifiers(['modifier_b']);

        $output = $pipeline->process(['message' => 'original']);

        $this->assertArrayNotHasKey('a', $output);
        $this->assertSame(2, $output['b']);
        $this->assertSame(0, $modifierA->called);
        $this->assertSame(1, $modifierB->called);
    }

    public function testProcessAppliesModifiersInOrder(): void
    {
        [$pipeline, $modifierA, $modifierB] = $this->createPipeline();

        $pipeline->setEnabledModifiers(['modifier_a', 'modifier_b']);

        $output = $pipeline->process(['message' => 'original']);

        $this->assertSame(1, $output['a']);
        $this->assertSame(2, $output['b']);
        $this->assertSame(1, $modifierA->called);
        $this->assertSame(1, $modifierB->called);
    }

    private function createPipeline(): array
    {
        $modifierA = new class implements LogModifierInterface {
            public int $called = 0;

            public function getId(): string
            {
                return 'modifier_a';
            }

            public function modify(array $log): array
            {
                $this->called++;
                $log['a'] = 1;

                return $log;
            }
        };

        $modifierB = new class implements LogModifierInterface {
            public int $called = 0;

            public function getId(): string
            {
                return 'modifier_b';
            }

            public function modify(array $log): array
            {
                $this->called++;
                $log['b'] = ($log['a'] ?? 1) + 1;

                return $log;
            }
        };

        $pipeline = new LogModificationPipeline([$modifierA, $modifierB]);

        return [$pipeline, $modifierA, $modifierB];
    }
}
