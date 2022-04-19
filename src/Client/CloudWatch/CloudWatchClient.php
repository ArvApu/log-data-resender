<?php

declare(strict_types=1);

namespace App\Client\CloudWatch;

use Aws\CloudWatchLogs\CloudWatchLogsClient;

class CloudWatchClient
{
    public function __construct(private CloudWatchLogsClient $client)
    {
    }

    public function getLogs(CloudWatchFilter $cloudWatchFilter): iterable
    {
        // TODO: change it to -> to array method??
        $logArguments = $cloudWatchFilter->jsonSerialize();

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