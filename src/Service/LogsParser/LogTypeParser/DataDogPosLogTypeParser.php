<?php

declare(strict_types=1);

namespace App\Service\LogsParser\LogTypeParser;

use App\Service\LogsParser\ParsedLog;

class DataDogPosLogTypeParser implements LogTypeParserInterface
{
    public static function getId(): string
    {
        return 'dd_pos';
    }

    public function parse(array $event): ?ParsedLog
    {
        $body = $event['attributes']['attributes']['info'] ?? null;
        $masterUserId = $event['attributes']['attributes']['usr']['id'] ?? null;

        if ($body === null || $masterUserId === null) {
            return null;
        }

        // TODO: method and url cannot be extracted from logs so we must find other way to get them, now hard-coding
        $method = 'POST';
        $url = 'https://pos-etail.wallmob.com/{ADD ENDPOINT HERE}';

        return new ParsedLog($body, $method, $url, json_decode($body)->id, $masterUserId);
    }
}