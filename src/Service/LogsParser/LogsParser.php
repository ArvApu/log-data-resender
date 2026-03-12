<?php

declare(strict_types=1);

namespace App\Service\LogsParser;

use App\Service\LogsModifier\LogModificationPipeline;
use App\Service\LogsParser\LogTypeParser\LogTypeParserInterface;

class LogsParser
{
    /**
     * @var LogTypeParserInterface[]
     */
    private readonly array $parsers;

    /**
     * If this parser is set, then all others will be ignored and only this will be used for logs parsing.
     */
    private ?LogTypeParserInterface $parser = null;

    public function __construct(
        private readonly LogModificationPipeline $logModificationPipeline,
        iterable $parsers
    ) {
        $indexedParsesList = [];

        foreach ($parsers as $parser) {
            if (!$parser instanceof LogTypeParserInterface) {
                throw new \LogicException(
                    'Log parser can only have parsers, that implements ' . LogTypeParserInterface::class
                );
            }

            $indexedParsesList[$parser::getId()] = $parser;
        }

        $this->parsers = $indexedParsesList;
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
            $log = $this->logModificationPipeline->process($log);

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
