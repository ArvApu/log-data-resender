<?php

declare(strict_types=1);

namespace App\LogParsers;

class POSLogParser extends LogParser
{
    public function parse(array $events): array
    {
        $parsed = [];

        foreach ($events as $event) {
            $info = json_decode($event['event']['json']['info']['info'] ?? '', true);

            if ($info === null) {
                continue;
            }

            parse_str($info['REQUEST_BODY'], $requestBody);

            $parsedLog = new ParsedLog(
                $this->decodeParametersFromObject($requestBody),
                $info['REQUEST_METHOD'],
                urldecode($info['REQUEST_URL']),
                $event['event']['json']['user']['id'] ?? null
            );

            array_push($parsed, $parsedLog);
        }

        return $parsed;
    }
}