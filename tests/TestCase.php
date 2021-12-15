<?php

declare(strict_types=1);

namespace Tests;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function getUnitFixturesDir(?string $path = null): string
    {
        $base = __DIR__ . '/Unit/Fixtures';

        if ($path === null) {
            return $base;
        }

        return "{$base}/{$path}";
    }
}