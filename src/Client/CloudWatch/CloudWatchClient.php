<?php

declare(strict_types=1);

namespace App\Client\CloudWatch;

use Aws\CloudWatchLogs\CloudWatchLogsClient;

class CloudWatchClient
{
    public function __construct(private CloudWatchLogsClient $client)
    {
    }

    public function getLogs(string $date, string $filterPattern, string $logGroupName, string $logStreamName): iterable
    {
        $date = new \DateTime($date);

        echo "Logs from day: {$date->format('Y-m-d H:i:s')} ({$date->getTimestamp()})" . PHP_EOL;

        $beginOfDay = strtotime('today', $date->getTimestamp());
        $endOfDay   = strtotime('tomorrow', $beginOfDay) - 1;

        $logArguments = [
            'startTime' => $beginOfDay * 1000,
            'endTime' => $endOfDay * 1000,
            'filterPattern' => $filterPattern,
            'logGroupName' => $logGroupName,
            'logStreamNamePrefix' => $logStreamName,
        ];

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