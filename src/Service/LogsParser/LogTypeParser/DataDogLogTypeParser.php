<?php

declare(strict_types=1);

namespace App\Service\LogsParser\LogTypeParser;

use App\Service\LogsParser\ParsedLog;

class DataDogLogTypeParser implements LogTypeParserInterface
{
    public static function getId(): string
    {
        return 'dd';
    }

    public function parse(array $event): ?ParsedLog
    {
        $request = $event['attributes']['attributes']['request'] ?? null;
        $params = $event['attributes']['attributes']['info']['api']['params'] ?? null;

        if ($params === null || $request === null) {
            return null;
        }

        return new ParsedLog(
            $params,
            $request['method'],
            $request['host'] . $request['url'],
            $event['attributes']['attributes']['info']['api']['id'],
            $event['attributes']['attributes']['user']['id'],
        );
    }
}
