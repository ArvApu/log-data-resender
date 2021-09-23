<?php

declare(strict_types=1);

namespace App\LogParsers;

class PosLogParser extends LogParser
{
    public function parse(array $data): array
    {
        $parsed = [];

        foreach ($data['events'] as $event) {
            $info = json_decode($event['event']['json']['info']['info'] ?? '', true);

            if ($info === null) {
                continue;
            }

            parse_str($info['REQUEST_BODY'], $requestBodyObject);

            array_push($parsed, $this->decodeParametersFromObject($requestBodyObject));
        }

        return $parsed;
    }
}