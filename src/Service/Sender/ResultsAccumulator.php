<?php

declare(strict_types=1);

namespace App\Service\Sender;

class ResultsAccumulator
{
    private array $counts = [];
    private array $errors = [];

    public function getCount(string $id): int
    {
        return $this->counts[$id] ?? 0;
    }

    public function getCounts(): array
    {
        return $this->counts;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function increment(string $id, int $incrementor = 1): void
    {
        if (!isset($this->counts[$id])) {
            $this->counts[$id] = 0;
        }

        $this->counts[$id] += $incrementor;
    }

    public function addError(array $error): void
    {
        $this->errors[] = $error;
    }
}
