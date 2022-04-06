<?php

declare(strict_types=1);

namespace App\Client\DataDog;

class Filter implements \JsonSerializable
{
    public function __construct(
        private string $query,
        private \DateTimeImmutable $from,
        private \DateTimeImmutable $to
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'query' => $this->query,
            'from' => $this->from->getTimestamp() * 1000,
            'to' => $this->to->getTimestamp() * 1000,
        ];
    }
}