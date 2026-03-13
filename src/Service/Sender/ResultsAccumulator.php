<?php

declare(strict_types=1);

namespace App\Service\Sender;

use App\Constant\Enum\ResultCategory;

class ResultsAccumulator
{
    private array $counts = [];

    public function getCount(ResultCategory $category): int
    {
        $id = $category->value;

        return $this->counts[$id] ?? 0;
    }

    public function getCounts(): array
    {
        return $this->counts;
    }

    public function increment(ResultCategory $category, int $incrementor = 1): void
    {
        $id = $category->value;

        if (!isset($this->counts[$id])) {
            $this->counts[$id] = 0;
        }

        $this->counts[$id] += $incrementor;
    }
}
