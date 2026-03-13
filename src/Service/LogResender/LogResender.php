<?php

declare(strict_types=1);

namespace App\Service\LogResender;

use App\Service\FileManager\FileManager;
use App\Service\LogParser\LogsParser;
use App\Service\LogProvider\LogsProvider;
use App\Service\Sender\ResultsAccumulator;
use App\Service\Sender\Sender;

readonly class LogResender
{
    public function __construct(
        private FileManager $filesManager,
        private Sender $sender,
        private LogsParser $logsParser,
        private LogsProvider $logsProvider,
    ) {
    }

    public function resend(string $source, string $filter, string $parser, array $modifiers): ?ResultsAccumulator
    {
        $results = null;

        $this->logsParser->setParsingStrategy($parser);
        $this->logsParser->setParsingModifiers($modifiers);

        $filters = $this->getFilters($filter);

        foreach ($filters as $key => $filter) {
            $logs = $this->logsProvider->getLogs($source, $filter);

            $parsedLogs = $this->logsParser->parse($logs);

            $results = $this->sender->sendData($parsedLogs);

            // Cleanup to save memory
            unset($parsedLogs);

            $this->filesManager->putContentsToFile("_counts-{$key}.json", json_encode($results->getCounts()));
        }

        return $results;
    }

    /**
     * Used to support reading files from directory,
     * so for provided filter value if it is string to dir it will return all files in that dir.
     *
     * Defaults to single file or filter string.
     *
     * NOTES:
     * 1. Lookup if this feature can be replaced.
     * 2. Can be used on CLI only, so maybe it should be moved to CLI command and not be part of service.
     *
     * @return string[]
     */
    private function getFilters(string $filter): array
    {
        $filters = is_dir($filter)
            ? array_map(
                fn (string $subFilter): string => "{$filter}/$subFilter",
                array_diff(scandir($filter), ['.', '..']),
            )
            : [$filter];

        sort($filters, SORT_NATURAL);

        return $filters;
    }
}