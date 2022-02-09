<?php

declare(strict_types=1);

namespace App\LogParser\Parser;

use App\LogParser\LogParserInterface;
use App\LogParser\ParsedLog;

class DataDogLogParser implements LogParserInterface
{
    public function parse(array $events): array
    {
        $parsed = [];

        foreach ($events as $event) {
            $request = $event['attributes']['attributes']['request'] ?? null;
            $params = $event['attributes']['attributes']['info']['api']['params'] ?? null;

            if ($params === null || $request === null) {
                continue;
            }

            $parsed[] = new ParsedLog(
                $params,
                $request['method'],
                $request['host'] . $request['url'],
                $event['attributes']['attributes']['info']['api']['id'],
                $event['attributes']['attributes']['user']['id'],
            );
        }

        return $parsed;
    }
}