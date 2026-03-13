<?php

declare(strict_types=1);

namespace App\Client\CloudWatch;

use Aws\CloudWatchLogs\CloudWatchLogsClient;

readonly class CloudWatchClient
{
    public function __construct(
        private CloudWatchLogsClient $client
    ) {
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
