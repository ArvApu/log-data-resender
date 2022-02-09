<?php

declare(strict_types=1);

namespace App\LogParser;

use App\LogParser\Parser\BackofficeLogParser;
use App\LogParser\Parser\DataDogLogParser;
use App\LogParser\Parser\POSLogParser;

class LogParserFactory
{
    public const POS_LOG_PARSER_TYPE = 'pos';
    public const BO_LOG_PARSER_TYPE  = 'bo';
    public const DD_LOG_PARSER_TYPE  = 'dd';

    public function getParser(string $type): LogParserInterface
    {
        return match ($type) {
            self::POS_LOG_PARSER_TYPE => new POSLogParser(),
            self::BO_LOG_PARSER_TYPE => new BackofficeLogParser(),
            self::DD_LOG_PARSER_TYPE => new DataDogLogParser(),
            default => throw new \InvalidArgumentException('Unsupported log parser type.'),
        };
    }
}
