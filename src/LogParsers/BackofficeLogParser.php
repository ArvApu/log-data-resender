<?php

declare(strict_types=1);

namespace App\LogParsers;

class BackofficeLogParser extends LogParser
{
    public function parse(array $events): array
    {
        $parsed = [];

        foreach ($events as $event) {
            $params = json_decode($event['event']['json']['info']['api']['params'] ?? '', true);

            if ($params === null) {
                continue;
            }

            $parsed[] = new ParsedLog(
                $this->decodeParametersFromObject($params),
                $event['event']['json']['request']['method'],
                $event['event']['json']['request']['host'] . $event['event']['json']['request']['url'],
                $event['event']['json']['user']['id'],
            );
        }

        return $parsed;
    }
}