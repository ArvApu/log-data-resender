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

            array_push($parsed, $this->decodeParametersFromObject($params));
        }

        return $parsed;
    }
}