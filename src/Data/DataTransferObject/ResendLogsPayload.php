<?php

declare(strict_types=1);

namespace App\Data\DataTransferObject;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class ResendLogsPayload
{
    public function __construct(
        public string $source,
        public string $parser,
        public string $filter = '',
        public ?string $datePreset = null,
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
        #[
            Assert\Unique,
        ]
        public array $modifiers = [],
    ) {
    }
}
