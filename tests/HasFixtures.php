<?php

declare(strict_types=1);

namespace Tests;

trait HasFixtures
{
    protected function getFixtures(?string $path = null): string
    {
        $base = __DIR__ . '/Fixtures';

        if ($path === null) {
            return $base;
        }

        return "{$base}/{$path}";
    }
}