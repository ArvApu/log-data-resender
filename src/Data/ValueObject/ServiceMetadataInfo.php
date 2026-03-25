<?php

declare(strict_types=1);

namespace App\Data\ValueObject;

final readonly class ServiceMetadataInfo
{
    public function __construct(
        private string $label,
        private string $description,
    ) {
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
