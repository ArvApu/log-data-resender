<?php

declare(strict_types=1);

namespace App;

class ResultsAccumulator
{
    private array $counts;
    private array $errors;
    private array $meta;

    public function __construct()
    {
        $this->counts = [];
        $this->errors = [];
        $this->meta   = [];
    }

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
        array_push($this->errors, $error);
    }

    public function addMeta(string $id, array $meta): void
    {
        array_push($this->meta[$id], $meta);
    }

    public function getMeta(?string $id = null): array
    {
        if ($id === null) {
            return $this->meta;
        }

        return $this->meta[$id] ?? [];
    }
}