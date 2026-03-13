<?php

declare(strict_types=1);

namespace App\Service\LogProvider\Source;

use App\Client\DataDog\DataDogClient;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(index: 'datadog')]
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