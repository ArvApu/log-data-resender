<?php

declare(strict_types=1);

namespace App\Client\CloudWatch;

use App\Service\LogsProvider\Source\LogsProviderSourceInterface;
use Aws\CloudWatchLogs\CloudWatchLogsClient;

class CloudWatchClient implements LogsProviderSourceInterface
{
    public function __construct(private CloudWatchLogsClient $client)
    {
    }

    public static function getId(): string
    {
        return 'cloudwatch';
    }

    public function getLogs(string $filter): iterable
    {
        $filter = CloudWatchFilter::fromJsonString($filter);

        $logArguments = $filter->jsonSerialize();

        // AWS might return empty events list, but with "next token", which indicates that they are still loading data
        do {
            $logs = $this->client->filterLogEvents($logArguments);

            foreach ($logs['events'] ?? [] as $event) {
                yield json_decode($event['message'], true);
            }

            $logArguments['nextToken'] = $logs['nextToken'];
        } while ($logs['nextToken'] !== null);
    }
}
