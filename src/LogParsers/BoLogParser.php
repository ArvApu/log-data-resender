<?php

declare(strict_types=1);

namespace App\LogParsers;

class BoLogParser extends LogParser
{
    public function parse(array $data): array
    {
        $parsed = [];

        foreach ($data['events'] as $event) {
            $params = json_decode($event['event']['json']['info']['api']['params'] ?? '', true);

            if ($params === null) {
                continue;
            }

            array_push($parsed, $this->decodeParametersFromObject($params));
        }

        return $parsed;
    }
}