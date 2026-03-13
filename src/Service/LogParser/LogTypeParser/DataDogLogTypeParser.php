<?php

declare(strict_types=1);

namespace App\Service\LogParser\LogTypeParser;

use App\Attribute\ServiceMetadata;
use App\Data\ValueObject\ParsedLog;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[
    AsTaggedItem(index: 'dd'),
    ServiceMetadata(label: 'DataDog Log Parser', description: 'Parses logs from DataDog.'),
]
class DataDogLogTypeParser implements LogTypeParserInterface
{
    public function parse(array $event): ?ParsedLog
    {
        $request = $event['attributes']['attributes']['request'] ?? null;
        $params = $event['attributes']['attributes']['info'] ?? null;

        if ($params === null || $request === null) {
            return null;
        }

        $params = json_decode($params, true);

        if (!isset($params['request_data'])) {
            return null;
        }

        return new ParsedLog(
            json_encode($params['request_data']),
            $request['method'],
            ($event['_host'] ?? $request['host']) . $request['url'],
            $event['attributes']['attributes']['api']['id'],
            $event['attributes']['attributes']['user']['id'],
        );
    }
}
