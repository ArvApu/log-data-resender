<?php

declare(strict_types=1);

namespace App\LogParser\Parser;

use App\LogParser\LogParserInterface;
use App\LogParser\ParsedLog;

class BackofficeLogParser implements LogParserInterface
{
    public function parse(array $events): array
    {
        $parsed = [];

        foreach ($events as $event) {
            $params = $event['event']['json']['info']['api']['params'] ?? null;

            if ($params === null) {
                continue;
            }

            $parsed[] = new ParsedLog(
                $params,
                $event['event']['json']['request']['method'],
                $event['event']['json']['request']['host'] . $event['event']['json']['request']['url'],
                $event['event']['json']['info']['api']['id'],
                $event['event']['json']['user']['id'],
            );
        }

        return $parsed;
    }
}
