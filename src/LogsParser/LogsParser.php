<?php

declare(strict_types=1);

namespace App\LogsParser;

use App\LogsParser\LogTypeParser\BackofficeLogTypeParser;
use App\LogsParser\LogTypeParser\CloudWatchLogTypeParser;
use App\LogsParser\LogTypeParser\DataDogLogTypeParser;
use App\LogsParser\LogTypeParser\LogTypeParserInterface;
use App\LogsParser\LogTypeParser\PosLogTypeParser;

class LogsParser
{
    // TODO: move to enum
    public const DD_LOG_TYPE_PARSER = 'dd';
    public const BO_LOG_TYPE_PARSER = 'bo';
    public const POS_LOG_TYPE_PARSER = 'pos';
    public const CW_LOG_TYPE_PARSER = 'cw';

    /**
     * @var LogTypeParserInterface[]
     */
    private array $parsers;

    /**
     * If this parser is set, then all others will be ignored and only this will be used for logs parsing.
     */
    private ?LogTypeParserInterface $parser = null;

    public function __construct(array $parsers)
    {
        $this->parsers = [];

        foreach ($parsers as $parser) {
            if (!$parser instanceof LogTypeParserInterface) {
                throw new \LogicException(
                    'Log parser can only have parsers, that implements ' . LogTypeParserInterface::class
                );
            }
        }

        $this->parsers = $parsers;
    }

    /**
     * Disables dynamic strategy selection for every log when parsing.
     * Use this method only where there is a need to use single parsing strategy for all logs.
     */
    public function setParsingStrategy(string $parserType): void
    {
        $this->parser = $this->parsers[$parserType]
            ?? throw new \InvalidArgumentException("Parser {$parserType} does not exist");
    }

    /**
     * @return ParsedLog[]
     */
    public function parse(iterable $logs): iterable
    {
        foreach ($logs as $log) {
            $parsedLog = $this->parseLog($log);

            if ($parsedLog === null) {
                continue;
            }

            yield $parsedLog;
        }
    }

    private function parseLog(array $log): ?ParsedLog
    {
        if ($this->parser !== null) {
            return $this->parser->parse($log);
        }

        foreach ($this->parsers as $parser) {
            $parsed = $parser->parse($log);

            if ($parsed === null) {
                continue;
            }

            return $parsed;
        }

        return null;
    }
}