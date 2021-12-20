<?php

declare(strict_types=1);

namespace App\LogParser;

use App\LogParser\Parser\BackofficeLogParser;
use App\LogParser\Parser\POSLogParser;

class LogParserFactory
{
    public const POS_LOG_PARSER_TYPE = 'pos';
    public const BO_LOG_PARSER_TYPE  = 'bo';

    public function getParser(string $type): LogParser
    {
        return match ($type) {
            self::POS_LOG_PARSER_TYPE => new POSLogParser(),
            self::BO_LOG_PARSER_TYPE => new BackofficeLogParser(),
            default => throw new \InvalidArgumentException('Unsupported log parser type.'),
        };
    }
}
