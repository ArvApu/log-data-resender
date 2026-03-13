<?php

declare(strict_types=1);

namespace App\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
readonly class ServiceMetadata
{
    public function __construct(
        public string $label,
        public string $description = '',
    ) {
    }
}