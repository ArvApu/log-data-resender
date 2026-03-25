<?php

declare(strict_types=1);

namespace App\Service\LogParser;

use App\Data\ValueObject\ParsedLog;
use App\Service\LogModifier\LogModificationPipeline;
use App\Service\LogParser\LogTypeParser\LogTypeParserInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ServiceLocator;

class LogsParser
{
    private LogTypeParserInterface $parser;

    public function __construct(
        #[AutowireLocator(LogTypeParserInterface::class)]
        private readonly ServiceLocator $serviceLocator,
        private readonly LogModificationPipeline $logModificationPipeline,
    ) {
    }

    public function setParsingStrategy(string $parserType): void
    {
        $this->parser = $this->serviceLocator->get($parserType);
    }

    public function setParsingModifiers(array $modifiers): void
    {
        $this->logModificationPipeline->setEnabledModifiers($modifiers);
    }

    /**
     * @return ParsedLog[]
     */
    public function parse(iterable $logs): iterable
    {
        if (!isset($this->parser)) {
            throw new \LogicException('Parsing strategy is not set');
        }

        foreach ($logs as $log) {
            $log = $this->logModificationPipeline->process($log);

            $parsedLog = $this->parser->parse($log);

            if ($parsedLog === null) {
                $parserClass = $this->parser::class;
                throw new \RuntimeException("Unable to parse log [$parserClass]");
            }

            yield $parsedLog;
        }
    }
}
