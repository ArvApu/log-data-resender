<?php

declare(strict_types=1);

namespace App\Data\ValueObject;

final readonly class DataDogPayload
{
    public function __construct(
        private ?string $query = null,
        private ?string $from = null,
        private ?string $to = null,
    ) {
    }

    #[\NoDiscard]
    public static function fromArray(array $payload): self
    {
        $filter = is_array($payload['filter'] ?? null) ? $payload['filter'] : [];

        return new self(
            isset($filter['query']) && is_string($filter['query']) ? $filter['query'] : null,
            isset($filter['from']) && is_string($filter['from']) ? $filter['from'] : null,
            isset($filter['to']) && is_string($filter['to']) ? $filter['to'] : null,
        );
    }

    #[\NoDiscard]
    public static function fromQuery(string $query): self
    {
        return new self(query: $query);
    }

    #[\NoDiscard]
    public function ensureRange(array $range): self
    {
        if ($this->from !== null && $this->to !== null) {
            return $this;
        }

        return new self(
            query: $this->query,
            from: $range['from'] ?? throw new \InvalidArgumentException('From date is required for DataDog payload.'),
            to: $range['to'] ?? throw new \InvalidArgumentException('To date is required for DataDog payload.'),
        );
    }

    public function toArray(): array
    {
        $filter = [];

        if ($this->query !== null && $this->query !== '') {
            $filter['query'] = $this->query;
        }

        if ($this->from !== null) {
            $filter['from'] = $this->from;
        }

        if ($this->to !== null) {
            $filter['to'] = $this->to;
        }

        return ['filter' => $filter];
    }

    /**
     * @throws \JsonException
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }
}
