<?php

declare(strict_types=1);

namespace App\Service\LogProvider\Source;

use App\Attribute\ServiceMetadata;
use App\Client\DataDog\DataDogClient;
use App\Constant\Enum\LogSource;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[
    AsTaggedItem(index: LogSource::DATADOG->value),
    ServiceMetadata(label: 'DataDog', description: 'Provides logs from DataDog.'),
]
readonly class DataDogSource implements LogsProviderSourceInterface
{
    public function __construct(
        private DataDogClient $client,
    ) {
    }

    public function getLogs(string $filter): iterable
    {
        return $this->client->getLogs($filter);
    }
}