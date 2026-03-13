<?php

declare(strict_types=1);

namespace App\Service\LogsProvider\Source;

use App\Client\CloudWatch\CloudWatchClient;

readonly class CloudWatchSource implements LogsProviderSourceInterface
{
    public function __construct(
        private CloudWatchClient $client,
    ) {
    }

    public static function getId(): string
    {
        return 'cloudwatch';
    }

    public function getLogs(string $filter): iterable
    {
        return $this->client->getLogs($filter);
    }
}