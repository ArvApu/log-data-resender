<?php

declare(strict_types=1);

namespace App\Service\LogProvider\Source;

use App\Attribute\ServiceMetadata;
use App\Client\CloudWatch\CloudWatchClient;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[
    AsTaggedItem(index: 'cloudwatch'),
    ServiceMetadata(label: 'CloudWatch', description: 'Provides logs from AWS CloudWatch.'),
]
readonly class CloudWatchSource implements LogsProviderSourceInterface
{
    public function __construct(
        private CloudWatchClient $client,
    ) {
    }

    public function getLogs(string $filter): iterable
    {
        return $this->client->getLogs($filter);
    }
}