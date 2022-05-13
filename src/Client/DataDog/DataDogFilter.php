<?php

declare(strict_types=1);

namespace App\Client\DataDog;

class DataDogFilter implements \JsonSerializable
{
    public function __construct(
        private string $query,
        private \DateTimeImmutable $from,
        private \DateTimeImmutable $to
    ) {
    }

    /**
     * @throws \Exception
     */
    public static function fromJsonString(string $json): self
    {
        $json = json_decode($json);

        if ($json === null || self::hasInvalidStructure($json)) {
            throw new \Exception('Invalid json string provided');
        }

        return new self(
            $json->query ?? $json->filter->query,
            new \DateTimeImmutable('@' . round(($json->from ?? $json->filter->from) / 1000)),
            new \DateTimeImmutable('@' . round(($json->to ?? $json->filter->to) / 1000)),
        );
    }

    private static function hasInvalidStructure(\stdClass $json): bool
    {
        return !isset($json->query, $json->from, $json->to)
            && !isset($json->filter->query, $json->filter->from, $json->filter->to);
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
