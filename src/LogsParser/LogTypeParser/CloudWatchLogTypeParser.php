<?php

declare(strict_types=1);

namespace App\LogsParser\LogTypeParser;

use App\LogsParser\ParsedLog;

class CloudWatchLogTypeParser implements LogTypeParserInterface
{
    public function parse(array $event): ?ParsedLog
    {
        $request = $event['request'] ?? null;
        $params = $event['info']['api']['params'] ?? null;

        if ($params === null || $request === null) {
            return null;
        }

        return new ParsedLog(
            $params,
            $request['method'],
            $request['host'] . $request['url'],
            $event['info']['api']['id'],
            $event['user']['id'],
        );
    }
}