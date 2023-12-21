<?php

declare(strict_types=1);

namespace App\Service\LogsParser\LogTypeParser;

use App\Service\LogsParser\ParsedLog;

/**
 * @deprecated
 */
class BackofficeLogTypeParser implements LogTypeParserInterface
{
    public static function getId(): string
    {
        return 'bo';
    }

    public function parse(array $event): ?ParsedLog
    {
        $params = $event['event']['json']['info']['api']['params'] ?? null;

        if ($params === null) {
            return null;
        }

        return new ParsedLog(
            $params,
            $event['event']['json']['request']['method'],
            $event['event']['json']['request']['host'] . $event['event']['json']['request']['url'],
            $event['event']['json']['info']['api']['id'],
            $event['event']['json']['user']['id'],
        );
    }
}
