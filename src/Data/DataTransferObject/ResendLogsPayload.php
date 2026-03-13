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
        #[
            Assert\Unique,
        ]
        public array $modifiers = [],
    ) {
    }
}