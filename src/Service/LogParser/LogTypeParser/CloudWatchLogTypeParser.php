<?php

declare(strict_types=1);

namespace App\Service\LogParser\LogTypeParser;

use App\Attribute\ServiceMetadata;
use App\Data\ValueObject\ParsedLog;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[
    AsTaggedItem(index: 'cw'),
    ServiceMetadata(label: 'CloudWatch Log Parser', description: 'Parses logs from AWS CloudWatch.'),
]
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
