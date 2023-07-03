<?php

declare(strict_types=1);

namespace App\Service\Sender;

class ResultsAccumulator
{
    private array $counts = [];

    public function getCount(string $id): int
    {
        return $this->counts[$id] ?? 0;
    }

    public function getCounts(): array
    {
        return $this->counts;
    }

    public function increment(string $id, int $incrementor = 1): void
    {
        if (!isset($this->counts[$id])) {
            $this->counts[$id] = 0;
        }

        $this->counts[$id] += $incrementor;
    }
}
