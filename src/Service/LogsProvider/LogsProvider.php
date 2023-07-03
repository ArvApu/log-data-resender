<?php

declare(strict_types=1);

namespace App\Service\LogsProvider;

use App\Service\LogsProvider\Source\LogsProviderSourceInterface;

class LogsProvider
{
    public function __construct(
        /** @var LogsProviderSourceInterface[] $sources */
        private readonly iterable $sources
    ) {
    }

    public function getLogs(string $source, string $filter): iterable
    {
        foreach ($this->sources as $availableSource) {
            if ($availableSource::getId() !== $source) {
                continue;
            }

            return $availableSource->getLogs($filter);
        }

        return [];
    }
}
