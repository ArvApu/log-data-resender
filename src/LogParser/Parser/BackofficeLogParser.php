<?php

declare(strict_types=1);

namespace App\LogParser\Parser;

use App\LogParser\LogParser;
use App\LogParser\ParsedLog;

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
                $params,
                $event['event']['json']['request']['method'],
                $event['event']['json']['request']['host'] . $event['event']['json']['request']['url'],
                $event['event']['json']['user']['id'],
            );
        }

        return $parsed;
    }
}